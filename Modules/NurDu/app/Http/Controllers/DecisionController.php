<?php

namespace Modules\NurDu\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\NurDu\Models\Decision;

class DecisionController extends Controller
{
    public function index(Request $request): View
    {
        $teamId = $request->user()->current_team_id;

        $decisions = Decision::where('team_id', $teamId)
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'green' => Decision::where('team_id', $teamId)->where('alignment', 'green')->count(),
            'yellow' => Decision::where('team_id', $teamId)->where('alignment', 'yellow')->count(),
            'red' => Decision::where('team_id', $teamId)->where('alignment', 'red')->count(),
        ];

        return view('nurdu::decisions.index', compact('decisions', 'stats'));
    }

    public function create(): View
    {
        return view('nurdu::decisions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'alignment' => ['required', 'in:green,yellow,red'],
            'justification' => ['nullable', 'string', 'max:2000'],
            'decision_date' => ['nullable', 'date'],
        ]);

        $user = $request->user();
        Decision::create([
            'team_id' => $user->current_team_id,
            'user_id' => $user->id,
        ] + $validated);

        return redirect()->route('nurdu.decisions.index')->with('success', __('Decision logged.'));
    }

    public function edit(Request $request, Decision $decision): View
    {
        abort_if($decision->team_id !== $request->user()->current_team_id, 403);

        return view('nurdu::decisions.edit', compact('decision'));
    }

    public function update(Request $request, Decision $decision): RedirectResponse
    {
        abort_if($decision->team_id !== $request->user()->current_team_id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'alignment' => ['required', 'in:green,yellow,red'],
            'justification' => ['nullable', 'string', 'max:2000'],
            'decision_date' => ['nullable', 'date'],
        ]);

        $decision->update($validated);

        return redirect()->route('nurdu.decisions.index')->with('success', __('Decision updated.'));
    }

    public function destroy(Request $request, Decision $decision): RedirectResponse
    {
        abort_if($decision->team_id !== $request->user()->current_team_id, 403);
        $decision->delete();

        return redirect()->route('nurdu.decisions.index')->with('success', __('Decision deleted.'));
    }
}
