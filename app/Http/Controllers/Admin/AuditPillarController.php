<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditTemplate;

class AuditPillarController extends Controller
{
    public function create(Request $request)
    {
        $template = AuditTemplate::withoutGlobalScope('current_team')->findOrFail($request->template_id);

        return view('admin.audits.pillars.create', compact('template'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:auditpro_templates,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'icon' => 'nullable|string|max:100',
            'target_score' => 'required|numeric|min:0|max:10',
            'position' => 'nullable|integer|min:0',
        ]);

        $template = AuditTemplate::withoutGlobalScope('current_team')->findOrFail($validated['template_id']);

        $template->pillars()->create([
            'team_id' => $template->team_id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'icon' => $validated['icon'],
            'target_score' => $validated['target_score'],
            'position' => $validated['position'] ?? 0,
        ]);

        return redirect()->route('admin.audits.templates.edit', $template)->with('success', __('admin.audit_pillars.created'));
    }

    public function edit(AuditPillar $pillar)
    {
        $pillar->load(['template.pillars', 'questions']);

        return view('admin.audits.pillars.edit', compact('pillar'));
    }

    public function update(Request $request, AuditPillar $pillar)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'icon' => 'nullable|string|max:100',
            'target_score' => 'required|numeric|min:0|max:10',
            'position' => 'nullable|integer|min:0',
        ]);

        $pillar->update($validated);

        return redirect()->route('admin.audits.templates.edit', $pillar->template_id)->with('success', __('admin.audit_pillars.updated'));
    }

    public function destroy(AuditPillar $pillar)
    {
        $templateId = $pillar->template_id;
        $pillar->delete();

        return redirect()->route('admin.audits.templates.edit', $templateId)->with('success', __('admin.audit_pillars.deleted'));
    }
}
