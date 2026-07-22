<?php

namespace Modules\FocusMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Modules\FocusMatrix\Models\SelfCheck;
use Modules\FocusMatrix\Models\Task;
use Modules\FocusMatrix\Services\Ai\AiManager;

class SelfCheckController extends Controller
{
    public function __construct(private readonly AiManager $ai) {}

    public function aiInsights(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = Carbon::now();
        $current = SelfCheck::where('user_id', $user->id)
            ->where('year', $now->year)->where('week', $now->weekOfYear)->first();
        if (! $current) {
            return response()->json(['ok' => false, 'message' => 'No current self-check found.'], 200);
        }

        $locale = $user->locale ?? app()->getLocale();
        $system = 'You are a coach for managers following the Only-You-Principle. '
            .'Given weekly self-check answers, produce 3-5 short, concrete insights and 1 focus recommendation for next week. '
            .'Return JSON: {insights: string[], next_week_focus: string}. Language: '.$locale.'.';
        $user_msg = "FOCUS SCORE: {$current->focus_score}%\n"
            .'Q1 Others could do: '.($current->q1_others_could_do ?? '(empty)')."\n"
            .'Q2 Delegated too late: '.($current->q2_delegated_late ?? '(empty)')."\n"
            .'Q3 To omit next week: '.($current->q3_to_omit_next_week ?? '(empty)')."\n"
            .'Q4 Focused decisions: '.($current->q4_focused_decisions ?? '(empty)');

        $json = $this->ai->promptJsonFor($user, $system, $user_msg);
        if (! $json) {
            return response()->json(['ok' => false, 'message' => 'AI not configured. Add your API key in Settings -> AI.'], 200);
        }

        return response()->json(['ok' => true, 'insights' => $json]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $now = Carbon::now();

        $current = SelfCheck::where('user_id', $user->id)
            ->where('year', $now->year)
            ->where('week', $now->weekOfYear)
            ->first();

        $history = SelfCheck::where('user_id', $user->id)
            ->orderByDesc('year')->orderByDesc('week')
            ->limit(10)
            ->get();

        return view('focusmatrix::self-check.index', compact('current', 'history', 'now'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $now = Carbon::now();

        $data = $request->validate([
            'q1_others_could_do' => ['nullable', 'string'],
            'q2_delegated_late' => ['nullable', 'string'],
            'q3_to_omit_next_week' => ['nullable', 'string'],
            'q4_focused_decisions' => ['nullable', 'string'],
        ]);

        $weekStart = $now->copy()->startOfWeek();
        $weekTasks = Task::where('user_id', $user->id)
            ->where('updated_at', '>=', $weekStart)->get();
        $kept = $weekTasks->where('status', Task::STATUS_KEEP)->count();
        $delegated = $weekTasks->where('status', Task::STATUS_DELEGATE)->count();
        $dropped = $weekTasks->where('status', Task::STATUS_DROP)->count();
        $total = max(1, $kept + $delegated + $dropped);
        $score = (int) round((($kept + $delegated) / $total) * 100);

        SelfCheck::updateOrCreate(
            ['user_id' => $user->id, 'year' => $now->year, 'week' => $now->weekOfYear],
            [...$data, 'team_id' => $user->current_team_id, 'focus_score' => $score]
        );

        return back()->with('success', __('Self-check saved. Have a great weekend.'));
    }
}
