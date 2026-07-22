<?php

namespace Modules\KpiTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\KpiTool\Models\KpiDefinition;

class KpiCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = KpiDefinition::query()->where('is_template', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $templates = $query->get();
        $categories = KpiDefinition::query()->where('is_template', true)->distinct()->pluck('category');

        return view('kpitool::catalog.index', compact('templates', 'categories'));
    }

    public function clone(KpiDefinition $kpiDefinition): RedirectResponse
    {
        $clone = $kpiDefinition->duplicateForTeam(
            auth()->user()->current_team_id,
            auth()->id()
        );

        return redirect()->route('kpitool.definitions.show', $clone)->with('success', __('KPI cloned.'));
    }
}
