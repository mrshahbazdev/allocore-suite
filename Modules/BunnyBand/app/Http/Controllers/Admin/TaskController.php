<?php

namespace Modules\BunnyBand\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\Task;
use Modules\BunnyBand\Models\Transaction;
use Modules\BunnyBand\Models\UserTask;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = Task::orderByDesc('created_at')->paginate(20);

        return view('bunnyband::admin.tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        return view('bunnyband::admin.tasks.form', ['task' => new Task]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Task::create($validated);

        return redirect()->route('bunnyband.admin.tasks.index')->with('success', __('Task created.'));
    }

    public function edit(Task $task): View
    {
        return view('bunnyband::admin.tasks.form', compact('task'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $task->update($validated);

        return redirect()->route('bunnyband.admin.tasks.index')->with('success', __('Task updated.'));
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('success', __('Task deleted.'));
    }

    public function submissions(Request $request): View
    {
        $query = UserTask::with(['profile.user', 'task'])->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'completed');
        }

        $submissions = $query->paginate(20)->withQueryString();

        return view('bunnyband::admin.tasks.submissions', compact('submissions'));
    }

    public function verify(Request $request, UserTask $userTask): RedirectResponse
    {
        $validated = $request->validate(['action' => 'required|in:approve,reject']);

        if ($validated['action'] === 'approve') {
            $userTask->update([
                'status' => 'verified',
                'verified_at' => now(),
            ]);

            $profile = $userTask->profile;
            $reward = $userTask->task->reward;
            if ($profile->level?->task_bonus_percent > 0) {
                $reward += $reward * ($profile->level->task_bonus_percent / 100);
            }

            $profile->increment('balance', $reward);
            $profile->increment('task_earnings', $reward);

            Transaction::create([
                'bunnyband_profile_id' => $profile->id,
                'type' => 'task_earning',
                'amount' => $reward,
                'status' => 'completed',
                'description' => __('Verified task: :title', ['title' => $userTask->task->title]),
                'processed_by' => auth()->id(),
            ]);
        } else {
            $userTask->update(['status' => 'rejected']);
        }

        return back()->with('success', $validated['action'] === 'approve' ? __('Task approved.') : __('Task rejected.'));
    }

    private function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:social_follow,app_install,website_visit,video_watch,game_play,daily_checkin',
            'reward' => 'required|numeric|min:0',
            'url' => 'nullable|url',
            'verification_method' => 'required|in:manual,automatic,timer',
            'is_active' => 'nullable|boolean',
            'max_completions' => 'nullable|integer|min:0',
            'cooldown_hours' => 'required|integer|min:0',
        ];
    }
}
