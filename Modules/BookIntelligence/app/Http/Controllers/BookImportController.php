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

                // Resolve Author (e.g. "Mike Michalowicz", "Diverse Autoren", "Biebig, Althof, Wagner")
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

                // Resolve Topics: Parent (Wissenswelt / Hauptwelt) and Subtopic (Thema)
                $wissenswelt = trim($row['wissenswelt'] ?? $row['hauptwelt'] ?? $row['main_topic'] ?? '');
                $thema = trim($row['thema'] ?? $row['category'] ?? $row['topic'] ?? $row['kategorie'] ?? $row['genre'] ?? '');

                $parentTopicId = null;
                if (!empty($wissenswelt)) {
                    $parentTopic = Topic::firstOrCreate(
                        ['team_id' => $teamId, 'name' => $wissenswelt, 'parent_id' => null],
                        ['slug' => Str::slug($wissenswelt) ?: 'wissenswelt']
                    );
                    if ($parentTopic->wasRecentlyCreated) {
                        $newTopics++;
                    }
                    $parentTopicId = $parentTopic->id;
                }

                $mainTopicId = $parentTopicId;
                if (!empty($thema)) {
                    $subTopic = Topic::firstOrCreate(
                        ['team_id' => $teamId, 'name' => $thema, 'parent_id' => $parentTopicId],
                        ['slug' => Str::slug(($wissenswelt ? $wissenswelt.'-' : '').$thema) ?: 'thema']
                    );
                    if ($subTopic->wasRecentlyCreated) {
                        $newTopics++;
                    }
                    $mainTopicId = $subTopic->id;
                }

                // Secondary Topics: Nebenwelt1 & Nebenwelt2
                $secondaryTopicIds = [];
                foreach (['nebenwelt1', 'nebenwelt2', 'nebenwelt', 'subtopic'] as $nwKey) {
                    $nwName = trim($row[$nwKey] ?? '');
                    if (!empty($nwName)) {
                        $nwTopic = Topic::firstOrCreate(
                            ['team_id' => $teamId, 'name' => $nwName],
                            ['slug' => Str::slug($nwName) ?: 'topic']
                        );
                        if ($nwTopic->wasRecentlyCreated) {
                            $newTopics++;
                        }
                        $secondaryTopicIds[] = $nwTopic->id;
                    }
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

                // Code / Reference identifier (e.g. FAC-001, FIN-001, FUE-001)
                $code = trim($row['code'] ?? $row['buch_code'] ?? $row['id_code'] ?? '');
                $isbn = trim($row['isbn'] ?? '');
                if (empty($isbn) && !empty($code)) {
                    $isbn = $code;
                }

                // Attributes
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

                // Relevanz / Difficulty mapping (Hoch -> Advanced, Mittel -> Intermediate, Niedrig -> Beginner)
                $rawRelevance = strtolower(trim($row['relevanz'] ?? $row['relevance'] ?? $row['priority'] ?? $row['difficulty'] ?? $row['level'] ?? ''));
                if (in_array($rawRelevance, ['hoch', 'high', 'advanced', 'expert'])) {
                    $difficulty = 'advanced';
                } elseif (in_array($rawRelevance, ['niedrig', 'low', 'beginner', 'grundlagen'])) {
                    $difficulty = 'beginner';
                } else {
                    $difficulty = 'intermediate';
                }

                $description = trim($row['description'] ?? $row['beschreibung'] ?? $row['summary'] ?? $row['zusammenfassung'] ?? '');
                if (!empty($code) && !str_contains($description, $code)) {
                    $description = "Katalog-Code: {$code}" . ($description ? "\n\n{$description}" : '');
                }

                $affiliateLink = trim($row['affiliate_link'] ?? $row['amazon_link'] ?? $row['link'] ?? $row['url'] ?? '');
                $relevantRolesText = trim($row['roles'] ?? $row['rollen'] ?? $row['relevant_roles'] ?? '');
                $roles = collect(preg_split('/[\r\n,;|]+/', $relevantRolesText))
                    ->map(fn ($r) => trim($r))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                // Check existing book by Title or Code/ISBN
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
                        'main_topic_id' => $mainTopicId ?: $existingBook->main_topic_id,
                        'isbn' => $isbn ?: $existingBook->isbn,
                        'publication_year' => $year ?: $existingBook->publication_year,
                        'page_count' => $pageCount ?: $existingBook->page_count,
                        'language' => $language ?: $existingBook->language,
                        'difficulty' => $difficulty ?: $existingBook->difficulty,
                        'description' => $description ?: $existingBook->description,
                        'affiliate_link' => $affiliateLink ?: $existingBook->affiliate_link,
                        'relevant_roles' => !empty($roles) ? $roles : $existingBook->relevant_roles,
                    ]);

                    if (!empty($secondaryTopicIds)) {
                        $existingBook->subtopics()->syncWithoutDetaching($secondaryTopicIds);
                    }

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
                    'main_topic_id' => $mainTopicId,
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

                if (!empty($secondaryTopicIds)) {
                    $book->subtopics()->sync($secondaryTopicIds);
                }

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

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="allocore_books_import_template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Column structure matching user's exact Excel catalog
            fputcsv($handle, [
                'Code',
                'Wissenswelt',
                'Thema',
                'Titel',
                'Autor',
                'Hauptwelt',
                'Nebenwelt1',
                'Nebenwelt2',
                'Relevanz',
                'Reading Status',
                'Notes',
            ]);

            // Sample rows based on user's exact catalog
            fputcsv($handle, [
                'FAC-001',
                'Fachwissen Logistik',
                'Gefahrgut',
                'ADR/RID',
                'Diverse Autoren',
                'Fachbibliothek',
                '',
                '',
                'Mittel',
                'planned',
                '',
            ]);

            fputcsv($handle, [
                'FIN-001',
                'Finanzen',
                'Cashflow',
                'Profit First',
                'Mike Michalowicz',
                'Finanzen',
                'Unternehmertum',
                '',
                'Hoch',
                'read',
                'Kernprinzip: Zuerst den Gewinn entnehmen.',
            ]);

            fputcsv($handle, [
                'FIN-002',
                'Finanzen',
                'Finanzbildung',
                'Rich Dad Poor Dad',
                'Robert T. Kiyosaki',
                'Finanzen',
                'Persönliche Entwicklung',
                '',
                'Hoch',
                'read',
                'Vermögenswerte vs. Verbindlichkeiten.',
            ]);

            fputcsv($handle, [
                'FUE-001',
                'Führung',
                'Leadership',
                'Führen ohne Fesseln',
                'Dirk Linn',
                'Führung',
                'Unternehmertum',
                '',
                'Hoch',
                'read',
                'Agiles Führen und Team-Autonomie.',
            ]);

            fputcsv($handle, [
                'FUE-004',
                'Führung',
                'Change Management',
                'Leading Change',
                'John P. Kotter',
                'Führung',
                'Innovation & Veränderung',
                'Unternehmertum',
                'Hoch',
                'reading',
                '8-Stufen-Modell für Veränderungsprozesse.',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    public function export(): StreamedResponse
    {
        $books = Book::with(['author', 'publisher', 'mainTopic', 'subtopics', 'currentUserProgress'])->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="allocore_books_library_export_'.date('Y-m-d').'.csv"',
        ];

        return response()->stream(function () use ($books) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Code',
                'Wissenswelt',
                'Thema',
                'Titel',
                'Autor',
                'Hauptwelt',
                'Nebenwelt1',
                'Nebenwelt2',
                'Relevanz',
                'Reading Status',
                'Progress Percent',
                'Reading Notes',
                'Language',
            ]);

            foreach ($books as $b) {
                $subtopicNames = $b->subtopics->pluck('name')->all();
                $neben1 = $subtopicNames[0] ?? '';
                $neben2 = $subtopicNames[1] ?? '';

                $relevanz = match ($b->difficulty) {
                    'advanced' => 'Hoch',
                    'beginner' => 'Niedrig',
                    default => 'Mittel',
                };

                $parentTopic = $b->mainTopic?->parent?->name ?? $b->mainTopic?->name ?? '';
                $subTopic = $b->mainTopic?->parent ? $b->mainTopic->name : '';

                fputcsv($handle, [
                    $b->isbn ?? '',
                    $parentTopic,
                    $subTopic,
                    $b->title,
                    $b->author?->name ?? '',
                    $parentTopic,
                    $neben1,
                    $neben2,
                    $relevanz,
                    $b->currentUserProgress?->status ?? 'unassigned',
                    $b->currentUserProgress?->progress_percent ?? 0,
                    $b->currentUserProgress?->notes ?? '',
                    $b->language,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
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


