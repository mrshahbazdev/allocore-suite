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
                    $author = $this->findOrCreateAuthor($teamId, $authorName);
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
                    $parentTopic = $this->findOrCreateTopic($teamId, $wissenswelt, null);
                    if ($parentTopic->wasRecentlyCreated) {
                        $newTopics++;
                    }
                    $parentTopicId = $parentTopic->id;
                }

                $mainTopicId = $parentTopicId;
                if (!empty($thema)) {
                    $subTopic = $this->findOrCreateTopic($teamId, $thema, $parentTopicId);
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
                        $nwTopic = $this->findOrCreateTopic($teamId, $nwName, null);
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
                    $publisher = $this->findOrCreatePublisher($teamId, $publisherName);
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

    private function parseCsvFile(string $path): array
    {
        $rows = [];
        if (!file_exists($path) || !is_readable($path)) {
            return [];
        }

        $sample = file_get_contents($path, false, null, 0, 2048);
        $delimiters = [';' => substr_count($sample, ';'), ',' => substr_count($sample, ','), "\t" => substr_count($sample, "\t")];
        arsort($delimiters);
        $delimiter = key($delimiters) ?: ',';

        if (($handle = fopen($path, 'r')) !== false) {
            $header = null;
            while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
                if ($header === null) {
                    $header = array_map(function ($h) {
                        return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
                    }, $data);
                    continue;
                }

                if (count($data) === 1 && empty($data[0])) {
                    continue;
                }

                $row = [];
                foreach ($header as $i => $colName) {
                    $row[$colName] = $data[$i] ?? null;
                }
                $rows[] = $row;
            }
            fclose($handle);
        }

        return $rows;
    }

    private function normalizeRowKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $k => $v) {
            $cleanKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $k)));
            $cleanKey = trim(preg_replace('/_+/', '_', $cleanKey), '_');
            $normalized[$cleanKey] = is_string($v) ? trim($v) : $v;
        }
        return $normalized;
    }

    private function syncImportedReadingProgress(Book $book, array $row): void
    {
        $rawStatus = strtolower(trim($row['reading_status'] ?? $row['lesestatus'] ?? $row['status_read'] ?? $row['status'] ?? ''));
        $progress = isset($row['progress_percent']) ? (int) $row['progress_percent'] : (isset($row['progress']) ? (int) $row['progress'] : null);
        $notes = trim($row['reading_notes'] ?? $row['notes'] ?? $row['notizen'] ?? '');

        $status = 'unassigned';
        if (in_array($rawStatus, ['read', 'gelesen', 'completed', 'fertig', 'done', 'yes', 'ja'])) {
            $status = 'read';
            $progress = 100;
        } elseif (in_array($rawStatus, ['reading', 'am lesen', 'current', 'in_progress', 'wip'])) {
            $status = 'reading';
            $progress = $progress !== null ? $progress : 50;
        } elseif (in_array($rawStatus, ['planned', 'geplant', 'to_read', 'toread', 'wishlist'])) {
            $status = 'planned';
            $progress = 0;
        }

        if ($status !== 'unassigned') {
            $book->progressRecords()->updateOrCreate(
                ['user_id' => auth()->id()],
                [
                    'team_id' => auth()->user()->current_team_id,
                    'status' => $status,
                    'progress_percent' => $progress ?: 0,
                    'started_at' => $status !== 'planned' ? now()->toDateString() : null,
                    'completed_at' => $status === 'read' ? now()->toDateString() : null,
                    'notes' => !empty($notes) ? $notes : null,
                ]
            );
        }
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

    private function findOrCreateTopic(int $teamId, string $name, ?int $parentId = null): Topic
    {
        $name = trim($name);

        // 1. Check existing by team and name under this parent (if parent provided)
        $query = Topic::where('team_id', $teamId)->where('name', $name);
        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        }
        $existing = $query->first();
        if ($existing) {
            return $existing;
        }

        // 2. Check existing by team and name under any parent
        $existingByName = Topic::where('team_id', $teamId)->where('name', $name)->first();
        if ($existingByName) {
            return $existingByName;
        }

        // 3. Check existing by slug
        $baseSlug = Str::slug($name) ?: 'topic';
        $existingBySlug = Topic::where('team_id', $teamId)->where('slug', $baseSlug)->first();
        if ($existingBySlug && mb_strtolower($existingBySlug->name) === mb_strtolower($name)) {
            return $existingBySlug;
        }

        // 4. Generate unique slug for this team
        $slug = $baseSlug;
        $suffix = 2;
        while (Topic::where('team_id', $teamId)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return Topic::create([
            'team_id' => $teamId,
            'user_id' => auth()->id(),
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    private function findOrCreateAuthor(int $teamId, string $name): Author
    {
        $name = trim($name);
        $existing = Author::where('team_id', $teamId)->where('name', $name)->first();
        if ($existing) {
            return $existing;
        }

        return Author::create([
            'team_id' => $teamId,
            'user_id' => auth()->id(),
            'name' => $name,
        ]);
    }

    private function findOrCreatePublisher(int $teamId, string $name): Publisher
    {
        $name = trim($name);
        $existing = Publisher::where('team_id', $teamId)->where('name', $name)->first();
        if ($existing) {
            return $existing;
        }

        return Publisher::create([
            'team_id' => $teamId,
            'user_id' => auth()->id(),
            'name' => $name,
        ]);
    }
}



