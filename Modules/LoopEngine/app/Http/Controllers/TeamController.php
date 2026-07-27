<?php

namespace Modules\LoopEngine\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LoopEngine\Models\Process;
use Modules\LoopEngine\Models\ProcessRun;
use Modules\LoopEngine\Models\TeamAssignment;
use Modules\LoopEngine\Services\WebhookService;

class TeamController extends Controller
{
    public function index(): View
    {
        $teamId = auth()->user()->current_team_id;

        $members = User::whereHas('teams', fn ($query) => $query->where('teams.id', $teamId))->paginate(10);
        $runsByMember = ProcessRun::where('team_id', $teamId)
            ->selectRaw('started_by, count(*) as count')
            ->groupBy('started_by')
            ->pluck('count', 'started_by');

        return view('loopengine::team.index', compact('members', 'runsByMember'));
    }

    public function createAssignment(): View
    {
        $processes = Process::where('status', 'active')->latest()->get();
        $users = auth()->user()->currentTeam->users ?? collect();

        return view('loopengine::team.assign', compact('processes', 'users'));
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'process_id' => 'required|exists:loopengine_processes,id',
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;
        $validated['assigned_by'] = auth()->id();
        $validated['assigned_at'] = now();

        TeamAssignment::updateOrCreate(
            ['process_id' => $validated['process_id'], 'user_id' => $validated['user_id']],
            $validated
        );

        $process = Process::find($validated['process_id']);
        app(WebhookService::class)->dispatch('assignment.created', [
            'process_id' => $process->id,
            'process' => $process->localizedName(),
            'user_id' => $validated['user_id'],
        ], $validated['team_id']);

        return redirect()->route('loopengine.team.assignments')->with('success', __('Assigned.'));
    }

    public function assignments(): View
    {
        $assignments = TeamAssignment::with('process', 'user')
            ->where('team_id', auth()->user()->current_team_id)
            ->latest('assigned_at')
            ->paginate(20);

        return view('loopengine::team.assignments', compact('assignments'));
    }

    public function editAssignment(TeamAssignment $assignment): View
    {
        $this->authorizeAssignment($assignment);

        $processes = Process::where('status', 'active')->latest()->get();
        $users = auth()->user()->currentTeam->users ?? collect();

        return view('loopengine::team.assignments-edit', compact('assignment', 'processes', 'users'));
    }

    public function updateAssignment(Request $request, TeamAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($assignment);

        $validated = $request->validate([
            'process_id' => 'required|exists:loopengine_processes,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string|in:pending,in_progress,completed',
            'notes' => 'nullable|string',
        ]);

        $validated['completed_at'] = $validated['status'] === 'completed' ? now() : null;

        $assignment->update($validated);

        return redirect()->route('loopengine.team.assignments')->with('success', __('Assignment updated.'));
    }

    public function destroyAssignment(TeamAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($assignment);
        $assignment->delete();

        return redirect()->route('loopengine.team.assignments')->with('success', __('Assignment deleted.'));
    }

    protected function authorizeAssignment(TeamAssignment $assignment): void
    {
        abort_if($assignment->team_id !== auth()->user()->current_team_id, 403);
    }
}
