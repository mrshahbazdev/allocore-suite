<?php

namespace Modules\FocusMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FocusMatrix\Models\Task;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->input('status', 'inbox');
        $validStatuses = ['inbox', 'keep', 'delegate', 'drop', 'done'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'inbox';
        }

        $tasks = Task::where('user_id', $user->id)
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();

        $counts = [
            'inbox' => Task::where('user_id', $user->id)->where('status', 'inbox')->count(),
            'keep' => Task::where('user_id', $user->id)->where('status', 'keep')->count(),
            'delegate' => Task::where('user_id', $user->id)->where('status', 'delegate')->count(),
            'drop' => Task::where('user_id', $user->id)->where('status', 'drop')->count(),
            'done' => Task::where('user_id', $user->id)->where('status', 'done')->count(),
        ];

        return view('focusmatrix::tasks.index', compact('tasks', 'status', 'counts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
        ]);

        Task::create([
            ...$data,
            'user_id' => $request->user()->id,
            'team_id' => $request->user()->current_team_id,
            'status' => Task::STATUS_INBOX,
            'source' => 'manual',
        ]);

        return back()->with('success', __('Task captured.'));
    }

    public function show(Task $task): View
    {
        $this->authorizeTask($task);
        $task->load('delegation.delegateUser');

        return view('focusmatrix::tasks.show', compact('task'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($task);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:inbox,keep,delegate,drop,done'],
            'only_you_category' => ['nullable', 'in:strategy,key_decisions,key_people,responsibility'],
            'due_at' => ['nullable', 'date'],
            'focused_block_at' => ['nullable', 'date'],
        ]);

        if (($data['status'] ?? null) === 'done') {
            $data['completed_at'] = now();
        }

        $task->update($data);

        return back()->with('success', __('Task updated.'));
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorizeTask($task);
        $task->delete();

        return back()->with('success', __('Task deleted.'));
    }

    private function authorizeTask(Task $task): void
    {
        abort_unless($task->user_id === request()->user()?->id, 403);
    }
}
