<?php

namespace Modules\AuditIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AuditIntelligence\Models\Finding;

class FindingController extends Controller
{
    public function index()
    {
        $findings = Finding::withCount(['recommendations', 'upsells'])->latest()->paginate(20);

        return view('auditintelligence::findings.index', compact('findings'));
    }

    public function create()
    {
        return view('auditintelligence::findings.form', ['finding' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateFinding($request);
        $finding = Finding::create($data['finding']);

        return redirect()->route('auditintelligence.findings.show', $finding)->with('message', __('Finding created.'));
    }

    public function show(Finding $finding)
    {
        $finding->load(['recommendations', 'upsells']);

        return view('auditintelligence::findings.show', compact('finding'));
    }

    public function edit(Finding $finding)
    {
        return view('auditintelligence::findings.form', compact('finding'));
    }

    public function update(Request $request, Finding $finding)
    {
        $data = $this->validateFinding($request);
        $finding->update($data['finding']);

        return redirect()->route('auditintelligence.findings.show', $finding)->with('message', __('Finding updated.'));
    }

    public function destroy(Finding $finding)
    {
        $finding->delete();

        return redirect()->route('auditintelligence.findings.index')->with('message', __('Finding deleted.'));
    }

    protected function validateFinding(Request $request): array
    {
        return $request->validate([
            'finding.audit_id' => ['nullable', 'integer'],
            'finding.title' => ['required', 'string', 'max:255'],
            'finding.description' => ['nullable', 'string'],
            'finding.risk_level' => ['required', 'in:low,medium,high,critical'],
            'finding.priority' => ['required', 'in:low,medium,high'],
            'finding.legal_relevance' => ['required', 'in:low,medium,high'],
            'finding.implementation_effort' => ['required', 'in:small,medium,large'],
            'finding.status' => ['required', 'in:open,in_progress,resolved,accepted'],
        ]);
    }
}
