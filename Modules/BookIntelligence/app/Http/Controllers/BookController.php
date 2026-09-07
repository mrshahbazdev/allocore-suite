<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\BookIntelligence\Models\Author;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\Publisher;
use Modules\BookIntelligence\Models\ReadingProgress;
use Modules\BookIntelligence\Models\Topic;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['author', 'mainTopic', 'currentUserProgress'])
            ->search($request->string('q')->toString());

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->string('difficulty')->toString());
        }

        if ($request->filled('topic')) {
            $topicId = $request->integer('topic');
            $query->where(function (Builder $builder) use ($topicId): void {
                $builder->where('main_topic_id', $topicId)
                    ->orWhereHas('subtopics', fn (Builder $subtopics) => $subtopics->whereKey($topicId));
            });
        }

        if ($request->filled('reading_status')) {
            $status = $request->string('reading_status')->toString();

            if ($status === 'unassigned') {
                $query->whereDoesntHave(
                    'progressRecords',
                    fn (Builder $progress) => $progress->where('user_id', auth()->id()),
                );
            } else {
                $query->whereHas(
                    'progressRecords',
                    fn (Builder $progress) => $progress
                        ->where('user_id', auth()->id())
                        ->where('status', $status),
                );
            }
        }

        match ($request->string('sort', 'alphabetical')->toString()) {
            'newest' => $query->latest(),
            'oldest' => $query->oldest(),
            'publication_year' => $query->orderByDesc('publication_year')->orderBy('title'),
            default => $query->orderBy('title'),
        };

        $books = $query->paginate(18)->withQueryString();
        $topics = Topic::whereNull('parent_id')->with('children')->orderBy('name')->get();

        return view('bookintelligence::books.index', compact('books', 'topics'));
    }

    public function create()
    {
        return view('bookintelligence::books.form', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateBook($request);

        $book = DB::transaction(function () use ($validated): Book {
            $book = Book::create($validated['book'] + [
                'slug' => $this->uniqueSlug($validated['book']['title']),
            ]);
            $book->subtopics()->sync($validated['subtopic_ids']);
            $this->saveReadingProgress($book, $validated['reading']);

            return $book;
        });

        return redirect()
            ->route('bookintelligence.books.show', $book)
            ->with('success', __('Book added to the knowledge library.'));
    }

    public function show(Book $book)
    {
        $book->load(['author', 'publisher', 'mainTopic', 'subtopics', 'currentUserProgress']);

        return view('bookintelligence::books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $book->load(['subtopics', 'currentUserProgress']);

        return view('bookintelligence::books.form', $this->formData($book));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $this->validateBook($request);

        DB::transaction(function () use ($book, $validated): void {
            $book->update($validated['book']);
            $book->subtopics()->sync($validated['subtopic_ids']);
            $this->saveReadingProgress($book, $validated['reading']);
        });

        return redirect()
            ->route('bookintelligence.books.show', $book)
            ->with('success', __('Book details updated.'));
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()
            ->route('bookintelligence.books.index')
            ->with('success', __('Book removed from the library.'));
    }

    private function formData(?Book $book = null): array
    {
        return [
            'book' => $book,
            'authors' => Author::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('name')->get(),
            'topics' => Topic::whereNull('parent_id')->with('children')->orderBy('name')->get(),
        ];
    }

    private function validateBook(Request $request): array
    {
        $teamId = $request->user()->current_team_id;
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author_id' => ['nullable', Rule::exists('bookintelligence_authors', 'id')->where('team_id', $teamId)],
            'publisher_id' => ['nullable', Rule::exists('bookintelligence_publishers', 'id')->where('team_id', $teamId)],
            'main_topic_id' => ['nullable', Rule::exists('bookintelligence_topics', 'id')->where('team_id', $teamId)],
            'subtopic_ids' => ['nullable', 'array'],
            'subtopic_ids.*' => [Rule::exists('bookintelligence_topics', 'id')->where('team_id', $teamId)],
            'isbn' => ['nullable', 'string', 'max:32'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:'.(now()->year + 1)],
            'page_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'language' => ['required', 'string', 'max:12'],
            'difficulty' => ['required', Rule::in(Book::DIFFICULTIES)],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['nullable', 'string'],
            'relevant_roles_text' => ['nullable', 'string'],
            'affiliate_link' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'archived'])],
            'reading_status' => ['nullable', Rule::in(['unassigned', ...ReadingProgress::STATUSES])],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'reading_notes' => ['nullable', 'string'],
        ]);

        $roles = collect(preg_split('/[\r\n,]+/', (string) ($validated['relevant_roles_text'] ?? '')))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'book' => [
                'title' => $validated['title'],
                'author_id' => $validated['author_id'] ?? null,
                'publisher_id' => $validated['publisher_id'] ?? null,
                'main_topic_id' => $validated['main_topic_id'] ?? null,
                'isbn' => $validated['isbn'] ?? null,
                'publication_year' => $validated['publication_year'] ?? null,
                'page_count' => $validated['page_count'] ?? null,
                'language' => $validated['language'],
                'difficulty' => $validated['difficulty'],
                'cover_url' => $validated['cover_url'] ?? null,
                'description' => $validated['description'] ?? null,
                'relevant_roles' => $roles,
                'affiliate_link' => $validated['affiliate_link'] ?? null,
                'status' => $validated['status'],
            ],
            'subtopic_ids' => $validated['subtopic_ids'] ?? [],
            'reading' => [
                'status' => $validated['reading_status'] ?? 'unassigned',
                'progress_percent' => (int) ($validated['progress_percent'] ?? 0),
                'notes' => $validated['reading_notes'] ?? null,
            ],
        ];
    }

    private function saveReadingProgress(Book $book, array $reading): void
    {
        if ($reading['status'] === 'unassigned') {
            $book->progressRecords()->where('user_id', auth()->id())->delete();

            return;
        }

        $existing = $book->progressRecords()->where('user_id', auth()->id())->first();
        $progress = $reading['status'] === 'read' ? 100 : $reading['progress_percent'];

        $book->progressRecords()->updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'team_id' => auth()->user()->current_team_id,
                'status' => $reading['status'],
                'progress_percent' => $progress,
                'started_at' => $reading['status'] === 'planned'
                    ? null
                    : ($existing?->started_at ?? now()->toDateString()),
                'completed_at' => $reading['status'] === 'read' ? now()->toDateString() : null,
                'notes' => $reading['notes'],
            ],
        );
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
