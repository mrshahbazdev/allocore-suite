<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlossaryTerm;
use App\Models\Module;
use Illuminate\Http\Request;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;

class AuditQuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditQuestion::withoutGlobalScope('current_team')
            ->with([
                'pillar' => fn ($q) => $q->withoutGlobalScope('current_team'),
                'template' => fn ($q) => $q->withoutGlobalScope('current_team'),
                'recommendedBook',
                'recommendedPost',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('failure_recommendation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('pillar_id')) {
            $query->where('pillar_id', $request->pillar_id);
        }

        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }

        $questions = $query->latest('id')->paginate(25)->withQueryString();
        $pillars = AuditPillar::withoutGlobalScope('current_team')->orderBy('name')->get();
        $templates = AuditTemplate::withoutGlobalScope('current_team')->orderBy('name')->get();

        return view('admin.audits.questions.index', compact('questions', 'pillars', 'templates'));
    }

    public function create(Request $request)
    {
        $templateId = $request->template_id;
        $pillar = $request->pillar_id ? AuditPillar::withoutGlobalScope('current_team')->find($request->pillar_id) : null;
        if (! $templateId && $pillar?->template_id) {
            $templateId = $pillar->template_id;
        }

        $template = $templateId ? AuditTemplate::withoutGlobalScope('current_team')->find($templateId) : null;
        $pillars = AuditPillar::withoutGlobalScope('current_team')
            ->when($template, fn ($q) => $q->where('template_id', $template->id))
            ->orderBy('position')
            ->get();

        if ($pillars->isEmpty()) {
            $pillars = AuditPillar::withoutGlobalScope('current_team')->orderBy('name')->get();
        }

        $templateQuestions = AuditQuestion::withoutGlobalScope('current_team')
            ->when($template, fn ($q) => $q->where('template_id', $template->id))
            ->get();

        $modules = Module::where('is_active', true)->orderBy('name')->get();
        $glossaryTerms = GlossaryTerm::published()->orderBy('term')->pluck('term', 'slug');
        $books = class_exists(\Modules\BookIntelligence\Models\Book::class)
            ? \Modules\BookIntelligence\Models\Book::query()->orderBy('title')->get()
            : collect();
        $posts = \App\Models\Post::query()->where('is_published', true)->orderBy('title')->get();

        return view('admin.audits.questions.create', compact('template', 'pillar', 'pillars', 'templateQuestions', 'modules', 'glossaryTerms', 'books', 'posts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'nullable|exists:auditpro_templates,id',
            'pillar_id' => 'required|exists:auditpro_pillars,id',
            'question' => 'required|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'question_type' => 'required|in:scale_1_to_5,yes_no,text,multiple_choice,number,file',
            'weight' => 'required|numeric|min:0|max:1000',
            'is_required' => 'nullable|boolean',
            'failure_recommendation' => 'nullable|string|max:2000',
            'recommended_module_key' => 'nullable|string|max:255',
            'knowledge_slug' => 'nullable|string|max:255',
            'recommended_book_id' => 'nullable|integer',
            'recommended_post_id' => 'nullable|integer',
            'options' => 'nullable|string|max:2000',
            'depends_on_question_id' => 'nullable|exists:auditpro_questions,id',
            'depends_on_answer' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
        ]);

        $pillar = AuditPillar::withoutGlobalScope('current_team')->findOrFail($validated['pillar_id']);
        $templateId = $validated['template_id'] ?? $pillar->template_id;
        $template = $templateId ? AuditTemplate::withoutGlobalScope('current_team')->find($templateId) : null;
        $teamId = $template?->team_id ?? $pillar->team_id;

        $options = $this->parseOptions($validated['options'] ?? null);

        AuditQuestion::create([
            'team_id' => $teamId,
            'template_id' => $templateId,
            'pillar_id' => $validated['pillar_id'],
            'question' => $validated['question'],
            'description' => $validated['description'],
            'question_type' => $validated['question_type'],
            'weight' => $validated['weight'],
            'is_required' => $request->boolean('is_required'),
            'failure_recommendation' => $validated['failure_recommendation'],
            'recommended_module_key' => $validated['recommended_module_key'] ?: null,
            'knowledge_slug' => $validated['knowledge_slug'] ?: null,
            'recommended_book_id' => $validated['recommended_book_id'] ?: null,
            'recommended_post_id' => $validated['recommended_post_id'] ?: null,
            'options' => $options,
            'depends_on_question_id' => $validated['depends_on_question_id'],
            'depends_on_answer' => $validated['depends_on_answer'],
            'position' => $validated['position'] ?? 0,
        ]);

        return redirect()->route('admin.audits.pillars.edit', $validated['pillar_id'])->with('success', __('admin.audit_questions.created'));
    }

    public function edit(AuditQuestion $question)
    {
        $template = null;
        if ($question->template_id) {
            $template = AuditTemplate::withoutGlobalScope('current_team')->find($question->template_id);
        }
        if (! $template && $question->pillar_id) {
            $pillar = AuditPillar::withoutGlobalScope('current_team')->find($question->pillar_id);
            if ($pillar?->template_id) {
                $template = AuditTemplate::withoutGlobalScope('current_team')->find($pillar->template_id);
            }
        }

        $pillars = AuditPillar::withoutGlobalScope('current_team')
            ->when($template, fn ($q) => $q->where('template_id', $template->id))
            ->orderBy('position')
            ->get();

        if ($pillars->isEmpty()) {
            $pillars = AuditPillar::withoutGlobalScope('current_team')->orderBy('name')->get();
        }

        $templateQuestions = AuditQuestion::withoutGlobalScope('current_team')
            ->when($template, fn ($q) => $q->where('template_id', $template->id))
            ->get();

        $modules = Module::where('is_active', true)->orderBy('name')->get();
        $glossaryTerms = GlossaryTerm::published()->orderBy('term')->pluck('term', 'slug');
        $books = class_exists(\Modules\BookIntelligence\Models\Book::class)
            ? \Modules\BookIntelligence\Models\Book::query()->orderBy('title')->get()
            : collect();
        $posts = \App\Models\Post::query()->where('is_published', true)->orderBy('title')->get();

        return view('admin.audits.questions.edit', compact('question', 'template', 'pillars', 'templateQuestions', 'modules', 'glossaryTerms', 'books', 'posts'));
    }

    public function update(Request $request, AuditQuestion $question)
    {
        $validated = $request->validate([
            'pillar_id' => 'required|exists:auditpro_pillars,id',
            'question' => 'required|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'question_type' => 'required|in:scale_1_to_5,yes_no,text,multiple_choice,number,file',
            'weight' => 'required|numeric|min:0|max:1000',
            'is_required' => 'nullable|boolean',
            'failure_recommendation' => 'nullable|string|max:2000',
            'recommended_module_key' => 'nullable|string|max:255',
            'knowledge_slug' => 'nullable|string|max:255',
            'recommended_book_id' => 'nullable|integer',
            'recommended_post_id' => 'nullable|integer',
            'options' => 'nullable|string|max:2000',
            'depends_on_question_id' => 'nullable|exists:auditpro_questions,id',
            'depends_on_answer' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
        ]);

        $options = $this->parseOptions($validated['options'] ?? null);

        $question->update([
            'pillar_id' => $validated['pillar_id'],
            'question' => $validated['question'],
            'description' => $validated['description'],
            'question_type' => $validated['question_type'],
            'weight' => $validated['weight'],
            'is_required' => $request->boolean('is_required'),
            'failure_recommendation' => $validated['failure_recommendation'],
            'recommended_module_key' => $validated['recommended_module_key'] ?: null,
            'knowledge_slug' => $validated['knowledge_slug'] ?: null,
            'recommended_book_id' => $validated['recommended_book_id'] ?: null,
            'recommended_post_id' => $validated['recommended_post_id'] ?: null,
            'options' => $options,
            'depends_on_question_id' => $validated['depends_on_question_id'],
            'depends_on_answer' => $validated['depends_on_answer'],
            'position' => $validated['position'] ?? 0,
        ]);

        // Propagate the recommendation assignments across all team instances of this question so dashboard & audits update globally
        AuditQuestion::withoutGlobalScope('current_team')
            ->where('question', $question->question)
            ->where('id', '!=', $question->id)
            ->update([
                'failure_recommendation' => $validated['failure_recommendation'],
                'recommended_module_key' => $validated['recommended_module_key'] ?: null,
                'knowledge_slug' => $validated['knowledge_slug'] ?: null,
                'recommended_book_id' => $validated['recommended_book_id'] ?: null,
                'recommended_post_id' => $validated['recommended_post_id'] ?: null,
            ]);

        return redirect()->route('admin.audits.pillars.edit', $question->pillar_id)->with('success', __('admin.audit_questions.updated'));
    }

    public function destroy(AuditQuestion $question)
    {
        $pillarId = $question->pillar_id;
        $question->delete();

        return redirect()->route('admin.audits.pillars.edit', $pillarId)->with('success', __('admin.audit_questions.deleted'));
    }

    private function parseOptions(?string $value): ?array
    {
        if (blank($value)) {
            return null;
        }

        $lines = array_filter(array_map('trim', explode(',', $value)));

        return $lines ?: null;
    }
}
