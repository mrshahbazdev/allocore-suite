<?php

namespace Modules\FocusMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FocusMatrix\Models\KillListItem;
use Modules\FocusMatrix\Models\Task;

class KillListController extends Controller
{
    public function index(Request $request): View
    {
        $items = KillListItem::where('user_id', $request->user()->id)
            ->latest('killed_at')
            ->get();

        return view('focusmatrix::kill-list.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'task_id' => ['nullable', 'exists:focusmatrix_tasks,id'],
            'item_type' => ['required', 'in:task,meeting,report,process,other'],
            'title' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string'],
            'was_necessary' => ['nullable', 'boolean'],
            'served_clear_goal' => ['nullable', 'boolean'],
            'anything_missing' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['task_id'])) {
            $task = Task::where('user_id', $request->user()->id)->find($data['task_id']);
            if ($task) {
                $task->update(['status' => Task::STATUS_DROP]);
            }
        }

        KillListItem::create([
            ...$data,
            'team_id' => $request->user()->current_team_id,
            'user_id' => $request->user()->id,
            'killed_at' => now(),
        ]);

        return redirect()->route('focusmatrix.kill-list.index')
            ->with('success', __('Boldly dropped.'));
    }

    public function destroy(Request $request, KillListItem $item): RedirectResponse
    {
        abort_unless($item->user_id === $request->user()->id, 403);
        $item->delete();

        return back();
    }
}
