<?php

namespace Modules\FocusMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FocusMatrix\Models\Delegation;
use Modules\FocusMatrix\Models\SelfCheck;
use Modules\FocusMatrix\Models\Task;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $team = $user->currentTeam;

        if (! $team) {
            return view('focusmatrix::analytics.index', ['team' => null]);
        }

        $memberIds = $team->members->pluck('id')->toArray();
        $memberIds[] = $team->owner_id;
        $memberIds = array_unique($memberIds);

        $taskStatusCounts = Task::whereIn('user_id', $memberIds)
            ->selectRaw('status, COUNT(*) as n')
            ->groupBy('status')
            ->pluck('n', 'status')
            ->toArray();

        $keep = (int) ($taskStatusCounts[Task::STATUS_KEEP] ?? 0);
        $delegate = (int) ($taskStatusCounts[Task::STATUS_DELEGATE] ?? 0);
        $drop = (int) ($taskStatusCounts[Task::STATUS_DROP] ?? 0);
        $done = (int) ($taskStatusCounts[Task::STATUS_DONE] ?? 0);
        $inbox = (int) ($taskStatusCounts[Task::STATUS_INBOX] ?? 0);
        $totalRouted = $keep + $delegate + $drop + $done;

        $latestPerMember = SelfCheck::whereIn('user_id', $memberIds)
            ->orderBy('user_id')
            ->orderByDesc('year')
            ->orderByDesc('week')
            ->get()
            ->unique('user_id')
            ->values();
        $teamFocusScore = $latestPerMember->count()
            ? (int) round($latestPerMember->avg('focus_score'))
            : 0;

        $twelveWeeksAgo = now()->subWeeks(12)->startOfWeek();
        $weeklyRaw = SelfCheck::whereIn('user_id', $memberIds)
            ->where('created_at', '>=', $twelveWeeksAgo)
            ->selectRaw('year, week, AVG(focus_score) as avg_score, COUNT(*) as n')
            ->groupBy('year', 'week')
            ->orderBy('year')
            ->orderBy('week')
            ->get();

        $weekly = $weeklyRaw->map(fn ($r) => [
            'label' => $r->year.'-W'.str_pad((string) $r->week, 2, '0', STR_PAD_LEFT),
            'score' => (int) round($r->avg_score),
            'n' => (int) $r->n,
        ])->values();

        $delegationsOpen = Delegation::whereIn('delegator_id', $memberIds)
            ->whereIn('status', ['open', 'in_progress'])->count();
        $delegationsDone = Delegation::whereIn('delegator_id', $memberIds)
            ->where('status', 'done')->count();
        $delegationsOverdue = Delegation::whereIn('delegator_id', $memberIds)
            ->where('status', 'overdue')->count();

        $topDelegators = Delegation::whereIn('delegator_id', $memberIds)
            ->selectRaw('delegator_id, COUNT(*) as n')
            ->groupBy('delegator_id')
            ->orderByDesc('n')
            ->limit(5)
            ->with('delegator:id,name')
            ->get()
            ->map(fn ($d) => [
                'user' => $d->delegator?->name ?? 'Unknown',
                'count' => (int) $d->n,
            ]);

        $categoryDist = Task::whereIn('user_id', $memberIds)
            ->where('status', Task::STATUS_KEEP)
            ->whereNotNull('only_you_category')
            ->selectRaw('only_you_category, COUNT(*) as n')
            ->groupBy('only_you_category')
            ->pluck('n', 'only_you_category')
            ->toArray();

        $memberStats = collect();
        $members = $team->members;
        if ($team->owner) {
            $members->prepend($team->owner);
        }
        foreach ($members->unique('id') as $m) {
            $taskCount = Task::where('user_id', $m->id)->count();
            $keepCount = Task::where('user_id', $m->id)->where('status', Task::STATUS_KEEP)->count();
            $delegCount = Task::where('user_id', $m->id)->where('status', Task::STATUS_DELEGATE)->count();
            $lastCheck = SelfCheck::where('user_id', $m->id)
                ->orderByDesc('year')->orderByDesc('week')->first();
            $memberStats->push([
                'name' => $m->name,
                'email' => $m->email,
                'tasks_total' => $taskCount,
                'keep' => $keepCount,
                'delegate' => $delegCount,
                'focus_score' => $lastCheck?->focus_score ?? null,
                'last_check_at' => $lastCheck?->created_at,
            ]);
        }

        return view('focusmatrix::analytics.index', [
            'team_name' => $team->name,
            'team_size' => count($memberIds),
            'team_focus_score' => $teamFocusScore,
            'task_distribution' => [
                'inbox' => $inbox,
                'keep' => $keep,
                'delegate' => $delegate,
                'drop' => $drop,
                'done' => $done,
                'total_routed' => $totalRouted,
            ],
            'category_distribution' => $categoryDist,
            'weekly_trend' => $weekly,
            'delegations' => [
                'open' => $delegationsOpen,
                'done' => $delegationsDone,
                'overdue' => $delegationsOverdue,
            ],
            'top_delegators' => $topDelegators,
            'member_stats' => $memberStats->sortByDesc('focus_score')->values(),
        ]);
    }
}
