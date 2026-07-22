<?php

namespace Modules\NurDu\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\NurDu\Models\QuarterlyFocus;
use Modules\NurDu\Models\StrategicPriority;

class QuarterlyFocusController extends Controller
{
    public function index(Request $request): View
    {
        $focuses = QuarterlyFocus::where('team_id', $request->user()->current_team_id)
            ->with('strategicPriorities')
            ->orderByDesc('year')
            ->orderByDesc('quarter')
            ->get();

        $currentQuarter = 'Q'.ceil(now()->month / 3);
        $currentYear = now()->year;

        return view('nurdu::quarterly.index', compact('focuses', 'currentQuarter', 'currentYear'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quarter' => ['required', 'in:Q1,Q2,Q3,Q4'],
            'year' => ['required', 'integer', 'min:2020', 'max:2040'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        QuarterlyFocus::updateOrCreate(
            [
                'team_id' => $user->current_team_id,
                'quarter' => $validated['quarter'],
                'year' => $validated['year'],
            ],
            ['user_id' => $user->id, 'notes' => $validated['notes']]
        );

        return redirect()->route('nurdu.quarterly.index')->with('success', __('Quarterly focus saved.'));
    }

    public function show(Request $request, QuarterlyFocus $quarterlyFocus): View
    {
        abort_if($quarterlyFocus->team_id !== $request->user()->current_team_id, 403);
        $quarterlyFocus->load('strategicPriorities');

        return view('nurdu::quarterly.show', compact('quarterlyFocus'));
    }

    public function destroy(Request $request, QuarterlyFocus $quarterlyFocus): RedirectResponse
    {
        abort_if($quarterlyFocus->team_id !== $request->user()->current_team_id, 403);
        $quarterlyFocus->delete();

        return redirect()->route('nurdu.quarterly.index')->with('success', __('Quarterly focus deleted.'));
    }

    public function storePriority(Request $request, QuarterlyFocus $quarterlyFocus): RedirectResponse
    {
        abort_if($quarterlyFocus->team_id !== $request->user()->current_team_id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'owner' => ['nullable', 'string', 'max:255'],
            'kpi' => ['nullable', 'string', 'max:255'],
        ]);

        $quarterlyFocus->strategicPriorities()->create($validated);

        return redirect()->route('nurdu.quarterly.show', $quarterlyFocus)->with('success', __('Strategic priority added.'));
    }

    public function updatePriority(Request $request, StrategicPriority $priority): RedirectResponse
    {
        $focus = $priority->quarterlyFocus;
        abort_if($focus->team_id !== $request->user()->current_team_id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'owner' => ['nullable', 'string', 'max:255'],
            'kpi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:on_track,at_risk,off_track'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $priority->update($validated);

        return redirect()->route('nurdu.quarterly.show', $focus)->with('success', __('Priority updated.'));
    }

    public function destroyPriority(Request $request, StrategicPriority $priority): RedirectResponse
    {
        $focus = $priority->quarterlyFocus;
        abort_if($focus->team_id !== $request->user()->current_team_id, 403);
        $priority->delete();

        return redirect()->route('nurdu.quarterly.show', $focus)->with('success', __('Priority removed.'));
    }
}
