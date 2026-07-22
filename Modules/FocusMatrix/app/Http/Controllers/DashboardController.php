<?php

namespace Modules\FocusMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Modules\FocusMatrix\Models\Delegation;
use Modules\FocusMatrix\Models\KillListItem;
use Modules\FocusMatrix\Models\SelfCheck;
use Modules\FocusMatrix\Models\Task;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $weekStart = Carbon::now()->startOfWeek();
        $weekTasks = Task::where('user_id', $user->id)
            ->where('updated_at', '>=', $weekStart)
            ->get();

        $kept = $weekTasks->where('status', Task::STATUS_KEEP)->count();
        $delegated = $weekTasks->where('status', Task::STATUS_DELEGATE)->count();
        $dropped = $weekTasks->where('status', Task::STATUS_DROP)->count();
        $total = max(1, $kept + $delegated + $dropped);
        $focusScore = (int) round((($kept + $delegated) / $total) * 100);

        $streak = $this->computeStreak($user->id);

        $recentTasks = Task::where('user_id', $user->id)
            ->latest()
            ->limit(6)
            ->get();

        $upcomingDelegations = Delegation::where('delegator_id', $user->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->orderBy('deadline')
            ->limit(5)
            ->with('delegateUser:id,name')
            ->get();

        $killCount = KillListItem::where('user_id', $user->id)->count();

        $categoryCounts = Task::where('user_id', $user->id)
            ->where('status', Task::STATUS_KEEP)
            ->whereNotNull('only_you_category')
            ->selectRaw('only_you_category, count(*) as count')
            ->groupBy('only_you_category')
            ->pluck('count', 'only_you_category');

        return view('focusmatrix::dashboard.index', [
            'stats' => [
                'focus_score' => $focusScore,
                'kept' => $kept,
                'delegated' => $delegated,
                'dropped' => $dropped,
                'kill_count' => $killCount,
                'streak' => $streak,
            ],
            'recent_tasks' => $recentTasks,
            'upcoming_delegations' => $upcomingDelegations,
            'category_counts' => $categoryCounts,
        ]);
    }

    private function computeStreak(int $userId): int
    {
        $checks = SelfCheck::where('user_id', $userId)
            ->orderByDesc('year')
            ->orderByDesc('week')
            ->limit(20)
            ->get(['year', 'week']);

        if ($checks->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $cursor = Carbon::now();
        foreach ($checks as $check) {
            if ((int) $cursor->year === (int) $check->year && (int) $cursor->weekOfYear === (int) $check->week) {
                $streak++;
                $cursor = $cursor->copy()->subWeek();
            } else {
                break;
            }
        }

        return $streak;
    }
}
