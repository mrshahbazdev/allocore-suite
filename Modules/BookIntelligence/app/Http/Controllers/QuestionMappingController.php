<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GlossaryTerm;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\QuestionMapping;
use Modules\BookIntelligence\Services\QuestionMappingGenerator;

class QuestionMappingController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $bookId = $request->query('book_id');
        $category = $request->query('category');

        $query = QuestionMapping::query()
            ->with(['book.author', 'book.mainTopic', 'auditQuestion', 'glossaryTerm'])
            ->search($search);

        if (filled($bookId)) {
            $query->where('book_id', $bookId);
        }

        if (filled($category)) {
            $query->where('category', $category);
        }

        $mappings = $query->latest()->paginate(15)->withQueryString();
        $books = Book::query()->orderBy('title')->get();
        $categories = QuestionMapping::query()->whereNotNull('category')->distinct()->pluck('category');

        return view('bookintelligence::questions.index', compact('mappings', 'books', 'categories', 'search', 'bookId', 'category'));
    }

    public function create(): View
    {
        $books = Book::query()->orderBy('title')->get();
        $modules = Module::query()->where('is_active', true)->orderBy('name')->get();
        $terms = GlossaryTerm::published()->orderBy('term')->get();
        $auditQuestions = class_exists(AuditQuestion::class)
            ? AuditQuestion::query()->orderBy('question')->take(100)->get()
            : collect();

        return view('bookintelligence::questions.form', [
            'mapping' => new QuestionMapping(),
            'books' => $books,
            'modules' => $modules,
            'terms' => $terms,
            'auditQuestions' => $auditQuestions,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => ['required', 'exists:bookintelligence_books,id'],
            'question' => ['required', 'string', 'max:500'],
            'answer_excerpt' => ['required', 'string'],
            'problem_statement' => ['nullable', 'string'],
            'target_audience' => ['nullable', 'array'],
            'when_to_read_trigger' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:64'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
            'audit_question_id' => ['nullable', 'integer'],
            'module_key' => ['nullable', 'string', 'max:64'],
            'tool_explanation' => ['nullable', 'string'],
            'glossary_term_id' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $book = Book::findOrFail($validated['book_id']);

        QuestionMapping::create([
            'team_id' => $book->team_id,
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'question' => $validated['question'],
            'answer_excerpt' => $validated['answer_excerpt'],
            'problem_statement' => $validated['problem_statement'] ?? null,
            'target_audience' => $validated['target_audience'] ?? [],
            'when_to_read_trigger' => $validated['when_to_read_trigger'] ?? null,
            'category' => $validated['category'] ?? null,
            'priority' => $validated['priority'] ?? 1,
            'audit_question_id' => $validated['audit_question_id'] ?? null,
            'module_key' => $validated['module_key'] ?? null,
            'tool_explanation' => $validated['tool_explanation'] ?? null,
            'glossary_term_id' => $validated['glossary_term_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('bookintelligence.questions.index')
            ->with('status', 'Question mapping created successfully.');
    }

    public function edit(QuestionMapping $question): View
    {
        $books = Book::query()->orderBy('title')->get();
        $modules = Module::query()->where('is_active', true)->orderBy('name')->get();
        $terms = GlossaryTerm::published()->orderBy('term')->get();
        $auditQuestions = class_exists(AuditQuestion::class)
            ? AuditQuestion::query()->orderBy('question')->take(100)->get()
            : collect();

        return view('bookintelligence::questions.form', [
            'mapping' => $question,
            'books' => $books,
            'modules' => $modules,
            'terms' => $terms,
            'auditQuestions' => $auditQuestions,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, QuestionMapping $question): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => ['required', 'exists:bookintelligence_books,id'],
            'question' => ['required', 'string', 'max:500'],
            'answer_excerpt' => ['required', 'string'],
            'problem_statement' => ['nullable', 'string'],
            'target_audience' => ['nullable', 'array'],
            'when_to_read_trigger' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:64'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
            'audit_question_id' => ['nullable', 'integer'],
            'module_key' => ['nullable', 'string', 'max:64'],
            'tool_explanation' => ['nullable', 'string'],
            'glossary_term_id' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $question->update([
            'book_id' => $validated['book_id'],
            'question' => $validated['question'],
            'answer_excerpt' => $validated['answer_excerpt'],
            'problem_statement' => $validated['problem_statement'] ?? null,
            'target_audience' => $validated['target_audience'] ?? [],
            'when_to_read_trigger' => $validated['when_to_read_trigger'] ?? null,
            'category' => $validated['category'] ?? null,
            'priority' => $validated['priority'] ?? 1,
            'audit_question_id' => $validated['audit_question_id'] ?? null,
            'module_key' => $validated['module_key'] ?? null,
            'tool_explanation' => $validated['tool_explanation'] ?? null,
            'glossary_term_id' => $validated['glossary_term_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('bookintelligence.questions.index')
            ->with('status', 'Question mapping updated successfully.');
    }

    public function destroy(QuestionMapping $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('bookintelligence.questions.index')
            ->with('status', 'Question mapping removed.');
    }

    public function generate(Book $book, QuestionMappingGenerator $generator): RedirectResponse
    {
        @set_time_limit(300);
        try {
            $mappings = $generator->generateForBook($book);

            return redirect()->route('bookintelligence.questions.index', ['book_id' => $book->id])
                ->with('status', count($mappings) . " AI question mappings & FAQs successfully generated for '{$book->title}'.");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['ai' => 'Failed to generate question mappings: ' . $e->getMessage()]);
        }
    }
}
