<?php

namespace Modules\NurDu\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\NurDu\Models\Decision;
use Modules\NurDu\Models\QuarterlyFocus;
use Modules\NurDu\Models\Vision;
use Modules\NurDu\Models\VisionCheck;

class NurDuController extends Controller
{
    public function index(): View
    {
        return view('nurdu::index');
    }

    public function demo(Request $request): RedirectResponse
    {
        $teamId = $request->user()->current_team_id;
        $userId = $request->user()->id;

        $vision = Vision::firstOrCreate(
            ['team_id' => $teamId],
            ['user_id' => $userId, 'statement' => 'We build a focused, values-driven company where every decision moves us closer to our long-term vision.']
        );

        if ($vision->guidingPrinciples()->count() === 0) {
            $vision->guidingPrinciples()->createMany([
                ['title' => 'Customer First', 'description' => 'Every decision serves the customer.', 'sort_order' => 1],
                ['title' => 'Radical Focus', 'description' => 'Say no to everything outside the current quarter.', 'sort_order' => 2],
                ['title' => 'Learn Fast', 'description' => 'Ship, measure and iterate weekly.', 'sort_order' => 3],
            ]);
        }

        $currentQuarter = 'Q'.ceil(now()->month / 3);
        $currentYear = now()->year;

        $focus = QuarterlyFocus::firstOrCreate(
            ['team_id' => $teamId, 'quarter' => $currentQuarter, 'year' => $currentYear],
            ['user_id' => $userId, 'notes' => 'This quarter is about clarifying the vision and aligning daily decisions.']
        );

        if ($focus->strategicPriorities()->count() === 0) {
            $focus->strategicPriorities()->createMany([
                ['title' => 'Finalize vision statement', 'owner' => 'CEO', 'kpi' => '100% team can recite it', 'status' => 'on_track'],
                ['title' => 'Quarterly decision audit', 'owner' => 'COO', 'kpi' => 'All decisions aligned', 'status' => 'at_risk'],
            ]);
        }

        if (Decision::where('team_id', $teamId)->count() === 0) {
            Decision::insert([
                ['team_id' => $teamId, 'user_id' => $userId, 'title' => 'Launch new onboarding flow', 'alignment' => 'green', 'decision_date' => now()->subDays(5), 'description' => 'Onboarding now explains our vision first.', 'justification' => 'Strengthens vision alignment from day one.', 'created_at' => now(), 'updated_at' => now()],
                ['team_id' => $teamId, 'user_id' => $userId, 'title' => 'Run broad marketing campaign', 'alignment' => 'red', 'decision_date' => now()->subDays(2), 'description' => 'Reached a wide but unfocused audience.', 'justification' => 'Diluted message, moved us away from core vision.', 'created_at' => now(), 'updated_at' => now()],
                ['team_id' => $teamId, 'user_id' => $userId, 'title' => 'Hire generalist consultant', 'alignment' => 'yellow', 'decision_date' => now()->subDay(), 'description' => 'Helped short term but not vision-specific.', 'justification' => 'Neutral impact on long-term vision.', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (VisionCheck::where('team_id', $teamId)->count() === 0) {
            $check = VisionCheck::create([
                'team_id' => $teamId,
                'user_id' => $userId,
                'check_date' => now()->subDays(3),
                'q1_answer' => 'partially',
                'q2_answer' => 'The broad marketing campaign pulled attention away from core values.',
                'q3_answer' => 'Pause broad campaigns and focus on our three principles.',
                'notes' => 'First vision check.',
            ]);

            $check->actionItems()->createMany([
                ['title' => 'Audit last 10 decisions against vision', 'completed' => false],
                ['title' => 'Rewrite marketing copy around principles', 'completed' => true],
            ]);
        }

        ActivityLog::log('created', 'Nur-Du demo data seeded', $vision, $request->user(), ['team_id' => $teamId]);

        return redirect()->route('nurdu.dashboard')->with('success', __('Demo data created. Explore Vision, Quarterly Focus, Decisions and Checks.'));
    }
}
