<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\BookIntelligence\Models\Author;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\Publisher;
use Modules\BookIntelligence\Models\ReadingProgress;
use Modules\BookIntelligence\Models\Topic;
use Spatie\SimpleExcel\SimpleExcelReader;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookImportController extends Controller
{
    public function index()
    {
        $bookCount = Book::count();
        $authorCount = Author::count();
        $topicCount = Topic::count();

        return view('bookintelligence::books.import', compact('bookCount', 'authorCount', 'topicCount'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:20480'],
            'duplicate_handling' => ['nullable', 'string', 'in:skip,update'],
        ]);

        $teamId = $request->user()->current_team_id;
        $file = $request->file('file');
        $duplicateHandling = $request->input('duplicate_handling', 'skip');

        $rows = [];
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if (in_array($extension, ['xlsx', 'xls']) && class_exists(SimpleExcelReader::class)) {
                $rows = SimpleExcelReader::create($file->getRealPath(), $extension)
                    ->getRows()
                    ->toArray();
            } else {
                $rows = $this->parseCsvFile($file->getRealPath());
            }
        } catch (\Throwable $e) {
            return back()->with('error', __('Failed to read the file: ').$e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', __('The uploaded file is empty or has no readable rows.'));
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $newAuthors = 0;
        $newTopics = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $rawRow) {
                $row = $this->normalizeRowKeys($rawRow);

                $title = trim($row['title'] ?? $row['titel'] ?? $row['book_title'] ?? $row['name'] ?? '');
                if (empty($title)) {
                    $skipped++;
                    continue;
                }

                // Resolve Author
                $authorName = trim($row['author'] ?? $row['autor'] ?? $row['author_name'] ?? '');
                $authorId = null;
                if (!empty($authorName)) {
                    $author = Author::firstOrCreate(
                        ['team_id' => $teamId, 'name' => $authorName],
                        ['slug' => Str::slug($authorName) ?: 'author']
                    );
                    if ($author->wasRecentlyCreated) {
                        $newAuthors++;
                    }
                    $authorId = $author->id;
                }

                // Resolve Main Topic / Category
                $topicName = trim($row['category'] ?? $row['topic'] ?? $row['kategorie'] ?? $row['genre'] ?? '');
                $topicId = null;
                if (!empty($topicName)) {
                    $topic = Topic::firstOrCreate(
                        ['team_id' => $teamId, 'name' => $topicName],
                        ['slug' => Str::slug($topicName) ?: 'topic']
                    );
                    if ($topic->wasRecentlyCreated) {
                        $newTopics++;
                    }
                    $topicId = $topic->id;
                }

                // Resolve Publisher
                $publisherName = trim($row['publisher'] ?? $row['verlag'] ?? '');
                $publisherId = null;
                if (!empty($publisherName)) {
                    $publisher = Publisher::firstOrCreate(
                        ['team_id' => $teamId, 'name' => $publisherName]
                    );
                    $publisherId = $publisher->id;
                }

                // Attributes
                $isbn = trim($row['isbn'] ?? '');
                $year = !empty($row['year'] ?? $row['jahr'] ?? $row['publication_year'] ?? null)
                    ? (int) ($row['year'] ?? $row['jahr'] ?? $row['publication_year'])
                    : null;
                $pageCount = !empty($row['pages'] ?? $row['seiten'] ?? $row['page_count'] ?? null)
                    ? (int) ($row['pages'] ?? $row['seiten'] ?? $row['page_count'])
                    : null;
                $language = trim($row['language'] ?? $row['sprache'] ?? 'de');
                if (strlen($language) > 12) {
                    $language = substr($language, 0, 12);
                }

                $difficulty = strtolower(trim($row['difficulty'] ?? $row['level'] ?? $row['schwierigkeit'] ?? 'intermediate'));
                if (!in_array($difficulty, Book::DIFFICULTIES)) {
                    $difficulty = 'intermediate';
                }

                $description = trim($row['description'] ?? $row['beschreibung'] ?? $row['summary'] ?? $row['zusammenfassung'] ?? '');
                $affiliateLink = trim($row['affiliate_link'] ?? $row['amazon_link'] ?? $row['link'] ?? $row['url'] ?? '');
                $relevantRolesText = trim($row['roles'] ?? $row['rollen'] ?? $row['relevant_roles'] ?? '');
                $roles = collect(preg_split('/[\r\n,;|]+/', $relevantRolesText))
                    ->map(fn ($r) => trim($r))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                // Check existing book by Title or ISBN
                $existingBook = Book::where(function ($q) use ($title, $isbn) {
                    $q->where('title', $title);
                    if (!empty($isbn)) {
                        $q->orWhere('isbn', $isbn);
                    }
                })->first();

                if ($existingBook) {
                    if ($duplicateHandling === 'skip') {
                        $skipped++;
                        continue;
                    }

                    $existingBook->update([
                        'author_id' => $authorId ?: $existingBook->author_id,
                        'publisher_id' => $publisherId ?: $existingBook->publisher_id,
                        'main_topic_id' => $topicId ?: $existingBook->main_topic_id,
                        'isbn' => $isbn ?: $existingBook->isbn,
                        'publication_year' => $year ?: $existingBook->publication_year,
                        'page_count' => $pageCount ?: $existingBook->page_count,
                        'language' => $language ?: $existingBook->language,
                        'difficulty' => $difficulty ?: $existingBook->difficulty,
                        'description' => $description ?: $existingBook->description,
                        'affiliate_link' => $affiliateLink ?: $existingBook->affiliate_link,
                        'relevant_roles' => !empty($roles) ? $roles : $existingBook->relevant_roles,
                    ]);

                    $this->syncImportedReadingProgress($existingBook, $row);
                    $updated++;
                    continue;
                }

                // Create new book
                $book = Book::create([
                    'team_id' => $teamId,
                    'title' => $title,
                    'slug' => $this->uniqueSlug($title),
                    'author_id' => $authorId,
                    'publisher_id' => $publisherId,
                    'main_topic_id' => $topicId,
                    'isbn' => $isbn ?: null,
                    'publication_year' => $year,
                    'page_count' => $pageCount,
                    'language' => $language ?: 'de',
                    'difficulty' => $difficulty,
                    'description' => $description ?: null,
                    'affiliate_link' => $affiliateLink ?: null,
                    'relevant_roles' => $roles,
                    'status' => 'active',
                ]);

                $this->syncImportedReadingProgress($book, $row);
                $imported++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', __('An error occurred during import: ').$e->getMessage());
        }

        $message = __(':imported books successfully imported.', ['imported' => $imported]);
        if ($updated > 0) {
            $message .= ' '.__(':updated existing books updated.', ['updated' => $updated]);
        }
        if ($skipped > 0) {
            $message .= ' '.__(':skipped skipped (duplicates/empty).', ['skipped' => $skipped]);
        }
        if ($newAuthors > 0 || $newTopics > 0) {
            $message .= ' '.__('(:authors new authors and :topics new categories auto-created).', ['authors' => $newAuthors, 'topics' => $newTopics]);
        }

        return redirect()
            ->route('bookintelligence.books.index')
            ->with('success', $message);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'book';
        $slug = $base;
        $suffix = 2;

        while (Book::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}

