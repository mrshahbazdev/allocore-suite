<?php

namespace Modules\CashCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\CashCore\Models\CashAlert;
use Modules\CashCore\Models\CashReview;
use Modules\CashCore\Services\CashCoreService;

class BehaviorController extends Controller
{
    public function __construct(private CashCoreService $service) {}

    public function index(): View
    {
        $this->service->generateAlerts(auth()->user()->current_team_id);

        $pendingReviews = CashReview::pending()->orderBy('scheduled_date')->get();
        $completedReviews = CashReview::completed()->latest('completed_date')->limit(10)->get();
        $alerts = CashAlert::unread()->latest()->get();
        $streak = CashReview::completed()->max('streak_count') ?? 0;

        return view('cashcore::behavior.index', compact('pendingReviews', 'completedReviews', 'alerts', 'streak'));
    }

    public function scheduleReview(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'review_type' => 'required|in:monthly,quarterly,annual',
            'scheduled_date' => 'required|date|after:today',
        ]);

        CashReview::create([
            'review_type' => $validated['review_type'],
            'scheduled_date' => $validated['scheduled_date'],
            'checklist' => CashReview::defaultChecklist(),
        ]);

        return back()->with('success', __('Review scheduled.'));
    }

    public function startReview(CashReview $review): View
    {
        $review->update(['status' => 'in_progress']);

        return view('cashcore::behavior.review', compact('review'));
    }

    public function completeReview(Request $request, CashReview $review): RedirectResponse
    {
        $checklist = $request->input('checklist', []);

        $lastCompleted = CashReview::completed()->latest('completed_date')->first();

        $streak = 1;
        if ($lastCompleted && $lastCompleted->completed_date->diffInDays(now()) <= 45) {
            $streak = $lastCompleted->streak_count + 1;
        }

        $review->update([
            'status' => 'completed',
            'completed_date' => now(),
            'checklist' => $checklist,
            'streak_count' => $streak,
        ]);

        return redirect()->route('cashcore.behavior.index')->with('success', __('Review completed.'));
    }

    public function markAlertRead(CashAlert $alert): RedirectResponse
    {
        $alert->markAsRead();

        return back();
    }

    public function dismissAlert(CashAlert $alert): RedirectResponse
    {
        $alert->update(['is_dismissed' => true]);

        return back();
    }
}
