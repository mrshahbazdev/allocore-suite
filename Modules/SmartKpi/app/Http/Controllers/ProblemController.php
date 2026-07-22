<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\SmartKpi\Models\KpiDefinition;
use Modules\SmartKpi\Models\Problem;

class ProblemController extends Controller
{
    public function index(Request $request): View
    {
        $query = Problem::with('kpiDefinition', 'company', 'department');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $problems = $query->latest()->paginate(15)->withQueryString();

        return view('smartkpi::problems.index', compact('problems'));
    }

    public function show(Problem $problem): View
    {
        $problem->load('kpiDefinition', 'actions.assignee');

        return view('smartkpi::problems.show', compact('problem'));
    }

    public function create(): View
    {
        return view('smartkpi::problems.form', [
            'problem' => new Problem,
            'kpis' => KpiDefinition::where('is_template', false)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['detected_by'] = auth()->id();
        $validated['detected_at'] = now();

        Problem::create($validated);

        return redirect()->route('smartkpi.problems.index')->with('success', __('Problem created.'));
    }

    public function edit(Problem $problem): View
    {
        return view('smartkpi::problems.form', [
            'problem' => $problem,
            'kpis' => KpiDefinition::where('is_template', false)->get(),
        ]);
    }

    public function update(Request $request, Problem $problem): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $problem->update($validated);

        return redirect()->route('smartkpi.problems.index')->with('success', __('Problem updated.'));
    }

    public function destroy(Problem $problem): RedirectResponse
    {
        $problem->delete();

        return redirect()->route('smartkpi.problems.index')->with('success', __('Problem deleted.'));
    }

    private function rules(): array
    {
        return [
            'kpi_definition_id' => 'required|exists:smartkpi_kpi_definitions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'severity' => 'required|string|in:warning,critical,anomaly',
            'status' => 'required|string|in:open,in_progress,resolved,closed',
        ];
    }
}
