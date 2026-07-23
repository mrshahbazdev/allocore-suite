<?php

namespace Modules\SweetSpot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SweetSpot\Models\SettingsWeight;
use Modules\SweetSpot\Services\SweetSpotScoringService;

class SettingsWeightController extends Controller
{
    public function index(Request $request)
    {
        $service = app(SweetSpotScoringService::class);
        $service->ensureDefaultWeights($request->user()->current_team_id);

        $weights = SettingsWeight::where('team_id', $request->user()->current_team_id)
            ->orderBy('criterion_key')
            ->get()
            ->keyBy('criterion_key');

        return view('sweetspot::settings.index', compact('weights'));
    }

    public function update(Request $request, SweetSpotScoringService $service)
    {
        $teamId = $request->user()->current_team_id;

        $validated = $request->validate([
            'weights' => 'required|array',
            'weights.*' => 'nullable|integer|min:0|max:10',
        ]);

        foreach ($validated['weights'] as $key => $weight) {
            SettingsWeight::updateOrCreate(
                ['team_id' => $teamId, 'criterion_key' => $key],
                ['weight' => (int) $weight]
            );
        }

        $service->calculateAll($teamId);

        return redirect()->route('sweetspot.settings.index')->with('success', __('Weights saved and scores recalculated.'));
    }
}
