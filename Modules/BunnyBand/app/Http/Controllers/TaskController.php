<?php

namespace Modules\BunnyBand\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\BunnyBandProfile;
use Modules\BunnyBand\Models\Task;
use Modules\BunnyBand\Models\Transaction;
use Modules\BunnyBand\Models\UserTask;

class TaskController extends Controller
{
    public function index(): View
    {
        $profile = BunnyBandProfile::forCurrentUser();
        $tasks = Task::where('is_active', true)->orderByDesc('created_at')->paginate(20);
        $myTasks = UserTask::where('bunnyband_profile_id', $profile->id)
            ->with('task')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('bunnyband::tasks.index', compact('tasks', 'myTasks'));
    }

    public function complete(Request $request, Task $task): RedirectResponse
    {
        $profile = BunnyBandProfile::forCurrentUser();

        $existing = UserTask::where('bunnyband_profile_id', $profile->id)
            ->where('bunnyband_task_id', $task->id)
            ->where('status', '!=', 'rejected')
            ->where('created_at', '>=', now()->subHours($task->cooldown_hours))
            ->first();

        if ($existing) {
            return back()->withErrors(['task' => __('Task already completed or on cooldown.')]);
        }

        $status = $task->verification_method === 'automatic' ? 'verified' : 'completed';

        $userTask = UserTask::create([
            'bunnyband_profile_id' => $profile->id,
            'bunnyband_task_id' => $task->id,
            'status' => $status,
            'completed_at' => now(),
            'verified_at' => $status === 'verified' ? now() : null,
        ]);

        if ($status === 'verified') {
            $reward = $task->reward;
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
                'description' => __('Completed task: :title', ['title' => $task->title]),
            ]);
        }

        return back()->with('success', $status === 'verified' ? __('Reward credited!') : __('Submitted for verification.'));
    }
}
