<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\AuditPro\Models\AuditTemplate;

class AuditTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = AuditTemplate::withoutGlobalScope('current_team')
            ->with(['team', 'creator'])
            ->withCount(['pillars', 'audits'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%');
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.audits.templates.index', compact('templates'));
    }

    public function create()
    {
        $teams = Team::orderBy('name')->get();

        return view('admin.audits.templates.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:auditpro_templates,slug',
            'description' => 'nullable|string|max:2000',
            'team_id' => 'required|exists:teams,id',
            'is_default' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['created_by'] = auth()->id();
        $validated['is_default'] = $request->boolean('is_default');

        AuditTemplate::create($validated);

        return redirect()->route('admin.audits.templates.index')->with('success', __('admin.audit_templates.created'));
    }

    public function show($template)
    {
        $templateId = $template instanceof AuditTemplate ? $template->id : $template;
        $template = AuditTemplate::withoutGlobalScope('current_team')
            ->with(['pillars.questions', 'team', 'creator'])
            ->findOrFail($templateId);

        return view('admin.audits.templates.show', compact('template'));
    }

    public function edit($template)
    {
        $templateId = $template instanceof AuditTemplate ? $template->id : $template;
        $template = AuditTemplate::withoutGlobalScope('current_team')
            ->with('pillars.questions')
            ->findOrFail($templateId);
        $teams = Team::orderBy('name')->get();

        return view('admin.audits.templates.edit', compact('template', 'teams'));
    }

    public function update(Request $request, $template)
    {
        $templateId = $template instanceof AuditTemplate ? $template->id : $template;
        $template = AuditTemplate::withoutGlobalScope('current_team')->findOrFail($templateId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:auditpro_templates,slug,'.$template->id,
            'description' => 'nullable|string|max:2000',
            'team_id' => 'required|exists:teams,id',
            'is_default' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_default'] = $request->boolean('is_default');

        $template->update($validated);

        return redirect()->route('admin.audits.templates.index')->with('success', __('admin.audit_templates.updated'));
    }

    public function destroy($template)
    {
        $templateId = $template instanceof AuditTemplate ? $template->id : $template;
        $template = AuditTemplate::withoutGlobalScope('current_team')->findOrFail($templateId);
        $template->delete();

        return redirect()->route('admin.audits.templates.index')->with('success', __('admin.audit_templates.deleted'));
    }
}
