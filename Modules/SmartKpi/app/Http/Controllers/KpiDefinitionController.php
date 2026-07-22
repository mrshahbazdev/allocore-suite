<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\SmartKpi\Models\Company;
use Modules\SmartKpi\Models\KpiDefinition;

class KpiDefinitionController extends Controller
{
    public function index(Request $request): View
    {
        $query = KpiDefinition::where('is_template', false)->with('company', 'department', 'latestValue');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request): void {
                $q->where('name_en', 'like', '%'.$request->search.'%')
                    ->orWhere('name_de', 'like', '%'.$request->search.'%');
            });
        }

        $definitions = $query->latest()->paginate(15)->withQueryString();

        return view('smartkpi::kpi-definitions.index', compact('definitions'));
    }

    public function create(): View
    {
        return view('smartkpi::kpi-definitions.form', [
            'kpi' => new KpiDefinition,
            'companies' => Company::active()->get(),
            'users' => User::whereHas('teams', fn ($q) => $q->where('teams.id', auth()->user()->current_team_id))->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['is_template'] = $request->boolean('is_template', false);

        $kpi = KpiDefinition::create($validated);
        $this->syncOwners($kpi, $request->input('owners', []));

        return redirect()->route('smartkpi.kpi-definitions.index')->with('success', __('KPI created.'));
    }

    public function show(KpiDefinition $kpiDefinition): View
    {
        $kpiDefinition->load('company', 'department', 'values.recorder', 'problems.actions', 'goals', 'forecasts', 'owners');

        return view('smartkpi::kpi-definitions.show', compact('kpiDefinition'));
    }

    public function edit(KpiDefinition $kpiDefinition): View
    {
        return view('smartkpi::kpi-definitions.form', [
            'kpi' => $kpiDefinition,
            'companies' => Company::active()->get(),
            'users' => User::whereHas('teams', fn ($q) => $q->where('teams.id', auth()->user()->current_team_id))->get(),
        ]);
    }

    public function update(Request $request, KpiDefinition $kpiDefinition): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['is_template'] = $request->boolean('is_template', $kpiDefinition->is_template);

        $kpiDefinition->update($validated);
        $this->syncOwners($kpiDefinition, $request->input('owners', []));

        return redirect()->route('smartkpi.kpi-definitions.index')->with('success', __('KPI updated.'));
    }

    public function destroy(KpiDefinition $kpiDefinition): RedirectResponse
    {
        $kpiDefinition->delete();

        return redirect()->route('smartkpi.kpi-definitions.index')->with('success', __('KPI deleted.'));
    }

    public function duplicate(KpiDefinition $kpiDefinition): RedirectResponse
    {
        $clone = $kpiDefinition->replicate();
        $clone->name_en .= ' '.__('Copy');
        $clone->name_de = ($kpiDefinition->name_de ? $kpiDefinition->name_de.' ' : '').'('.__('Copy').')';
        $clone->is_template = false;
        $clone->save();

        return redirect()->route('smartkpi.kpi-definitions.edit', $clone)->with('success', __('KPI duplicated.'));
    }

    private function rules(): array
    {
        return [
            'company_id' => 'required|exists:smartkpi_companies,id',
            'department_id' => 'nullable|exists:smartkpi_departments,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'name_en' => 'required|string|max:255',
            'name_de' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_de' => 'nullable|string',
            'formula' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'target_value' => 'nullable|numeric',
            'warning_threshold' => 'nullable|numeric',
            'critical_threshold' => 'nullable|numeric',
            'frequency' => 'nullable|string|max:50',
            'direction' => 'nullable|in:asc,desc',
            'category' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }

    private function syncOwners(KpiDefinition $kpi, array $userIds): void
    {
        $pivot = collect($userIds)->mapWithKeys(fn ($id) => [$id => ['role' => 'viewer']]);
        $kpi->owners()->sync($pivot);
    }
}
