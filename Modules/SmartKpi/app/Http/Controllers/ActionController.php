<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\SmartKpi\Models\Action;
use Modules\SmartKpi\Models\Problem;

class ActionController extends Controller
{
    public function create(Problem $problem): View
    {
        $users = User::whereHas('teams', fn ($q) => $q->where('teams.id', auth()->user()->current_team_id))->get();

        return view('smartkpi::actions.form', ['action' => new Action, 'problem' => $problem, 'users' => $users]);
    }

    public function store(Request $request, Problem $problem): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['problem_id'] = $problem->id;
        $validated['team_id'] = auth()->user()->current_team_id;

        Action::create($validated);

        return redirect()->route('smartkpi.problems.show', $problem)->with('success', __('Action created.'));
    }

    public function edit(Action $action): View
    {
        $users = User::whereHas('teams', fn ($q) => $q->where('teams.id', auth()->user()->current_team_id))->get();

        return view('smartkpi::actions.form', compact('action', 'users'));
    }

    public function update(Request $request, Action $action): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $action->update($validated);

        return redirect()->route('smartkpi.problems.show', $action->problem)->with('success', __('Action updated.'));
    }

    public function destroy(Action $action): RedirectResponse
    {
        $problem = $action->problem;
        $action->delete();

        return redirect()->route('smartkpi.problems.show', $problem)->with('success', __('Action deleted.'));
    }

    private function rules(): array
    {
        return [
            'assigned_to' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|in:open,in_progress,done,cancelled',
            'effectiveness_score' => 'nullable|integer|min:0|max:100',
        ];
    }
}
