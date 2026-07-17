<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;

class AuditQuestionController extends Controller
{
    public function create(Request $request)
    {
        $template = AuditTemplate::withoutGlobalScope('current_team')->with(['pillars', 'questions'])->findOrFail($request->template_id);
        $pillar = $request->pillar_id ? AuditPillar::withoutGlobalScope('current_team')->findOrFail($request->pillar_id) : null;
        $pillars = $template->pillars;

        return view('admin.audits.questions.create', compact('template', 'pillar', 'pillars'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:auditpro_templates,id',
            'pillar_id' => 'required|exists:auditpro_pillars,id',
            'question' => 'required|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'question_type' => 'required|in:scale_1_to_5,yes_no,text,multiple_choice,number,file',
            'weight' => 'required|numeric|min:0|max:1000',
            'is_required' => 'nullable|boolean',
            'failure_recommendation' => 'nullable|string|max:2000',
            'options' => 'nullable|string|max:2000',
            'depends_on_question_id' => 'nullable|exists:auditpro_questions,id',
            'depends_on_answer' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
        ]);

        $template = AuditTemplate::withoutGlobalScope('current_team')->findOrFail($validated['template_id']);

        $options = $this->parseOptions($validated['options'] ?? null);

        AuditQuestion::create([
            'team_id' => $template->team_id,
            'template_id' => $validated['template_id'],
            'pillar_id' => $validated['pillar_id'],
            'question' => $validated['question'],
            'description' => $validated['description'],
            'question_type' => $validated['question_type'],
            'weight' => $validated['weight'],
            'is_required' => $request->boolean('is_required'),
            'failure_recommendation' => $validated['failure_recommendation'],
            'options' => $options,
            'depends_on_question_id' => $validated['depends_on_question_id'],
            'depends_on_answer' => $validated['depends_on_answer'],
            'position' => $validated['position'] ?? 0,
        ]);

        return redirect()->route('admin.audits.pillars.edit', $validated['pillar_id'])->with('success', __('admin.audit_questions.created'));
    }

    public function edit(AuditQuestion $question)
    {
        $question->load(['template.pillars', 'template.questions', 'pillar.questions']);
        $template = $question->template;
        $pillars = $template->pillars;

        return view('admin.audits.questions.edit', compact('question', 'template', 'pillars'));
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
            'options' => $options,
            'depends_on_question_id' => $validated['depends_on_question_id'],
            'depends_on_answer' => $validated['depends_on_answer'],
            'position' => $validated['position'] ?? 0,
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
