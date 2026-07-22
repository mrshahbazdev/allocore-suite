<?php

namespace Modules\KpiTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\KpiTool\Models\KpiDefinition;

class KpiDefinitionController extends Controller
{
    public function index(Request $request): View
    {
        $query = KpiDefinition::query()->where('is_template', false)->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request): void {
                $q->where('name_de', 'like', '%'.$request->search.'%')
                    ->orWhere('name_en', 'like', '%'.$request->search.'%');
            });
        }

        $definitions = $query->latest()->paginate(15)->withQueryString();
        $categories = KpiDefinition::query()->distinct()->pluck('category');

        return view('kpitool::definitions.index', compact('definitions', 'categories'));
    }

    public function create(): View
    {
        return view('kpitool::definitions.form', ['definition' => new KpiDefinition]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        KpiDefinition::create($validated);

        return redirect()->route('kpitool.definitions.index')->with('success', __('KPI created.'));
    }

    public function show(KpiDefinition $definition): View
    {
        $definition->load(['values' => fn ($query) => $query->latest('recorded_at')->take(24), 'monthlyTargets']);

        $values = $definition->values->sortBy('recorded_at')->values();

        return view('kpitool::definitions.show', compact('definition', 'values'));
    }

    public function edit(KpiDefinition $definition): View
    {
        return view('kpitool::definitions.form', compact('definition'));
    }

    public function update(Request $request, KpiDefinition $definition): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $definition->update($validated);

        return redirect()->route('kpitool.definitions.show', $definition)->with('success', __('KPI updated.'));
    }

    public function destroy(KpiDefinition $definition): RedirectResponse
    {
        $definition->delete();

        return redirect()->route('kpitool.definitions.index')->with('success', __('KPI deleted.'));
    }

    private function rules(): array
    {
        return [
            'name_de' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_de' => 'nullable|string',
            'description_en' => 'nullable|string',
            'formula' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'target_value' => 'nullable|numeric',
            'warning_threshold' => 'nullable|numeric',
            'critical_threshold' => 'nullable|numeric',
            'frequency' => 'nullable|string|in:daily,weekly,monthly,quarterly,yearly',
            'direction' => 'nullable|string|in:higher_better,lower_better',
            'category' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
