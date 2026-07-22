<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\SmartKpi\Models\Forecast;
use Modules\SmartKpi\Models\KpiDefinition;
use Modules\SmartKpi\Services\ForecastService;

class ForecastController extends Controller
{
    public function __construct(protected ForecastService $service) {}

    public function store(Request $request, KpiDefinition $kpiDefinition): RedirectResponse
    {
        $validated = $request->validate([
            'horizon' => 'required|string|max:50',
            'method' => 'required|string|in:linear,exponential',
        ]);

        $this->service->forecast($kpiDefinition, $validated['horizon'], $validated['method']);

        return redirect()->route('smartkpi.kpi-definitions.show', $kpiDefinition)->with('success', __('Forecast generated.'));
    }

    public function show(Forecast $forecast): View
    {
        $forecast->load('kpiDefinition');

        return view('smartkpi::forecasts.show', compact('forecast'));
    }
}
