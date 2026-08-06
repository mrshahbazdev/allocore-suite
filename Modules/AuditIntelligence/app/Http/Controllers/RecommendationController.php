<?php

namespace Modules\AuditIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AuditIntelligence\Models\Finding;
use Modules\AuditIntelligence\Models\Recommendation;

class RecommendationController extends Controller
{
    public function index(Finding $finding)
    {
        $recommendations = $finding->recommendations()->latest()->paginate(20);

        return view('auditintelligence::recommendations.index', compact('finding', 'recommendations'));
    }

    public function create(Finding $finding)
    {
        return view('auditintelligence::recommendations.form', ['finding' => $finding, 'recommendation' => null]);
    }

    public function store(Request $request, Finding $finding)
    {
        $data = $this->validateRecommendation($request);
        $finding->recommendations()->create($data['recommendation']);

        return redirect()->route('auditintelligence.findings.show', $finding)->with('message', __('Recommendation created.'));
    }

    public function show(Finding $finding, Recommendation $recommendation)
    {
        return view('auditintelligence::recommendations.show', compact('finding', 'recommendation'));
    }

    public function edit(Finding $finding, Recommendation $recommendation)
    {
        return view('auditintelligence::recommendations.form', compact('finding', 'recommendation'));
    }

    public function update(Request $request, Finding $finding, Recommendation $recommendation)
    {
        $data = $this->validateRecommendation($request);
        $recommendation->update($data['recommendation']);

        return redirect()->route('auditintelligence.findings.show', $finding)->with('message', __('Recommendation updated.'));
    }

    public function destroy(Finding $finding, Recommendation $recommendation)
    {
        $recommendation->delete();

        return redirect()->route('auditintelligence.findings.show', $finding)->with('message', __('Recommendation deleted.'));
    }

    protected function validateRecommendation(Request $request): array
    {
        return $request->validate([
            'recommendation.issue' => ['required', 'string'],
            'recommendation.solution' => ['nullable', 'string'],
            'recommendation.responsible' => ['nullable', 'string', 'max:255'],
            'recommendation.effort' => ['required', 'in:small,medium,large'],
            'recommendation.related_sop' => ['nullable', 'string', 'max:255'],
            'recommendation.status' => ['required', 'in:pending,accepted,implemented,dismissed'],
        ]);
    }
}
