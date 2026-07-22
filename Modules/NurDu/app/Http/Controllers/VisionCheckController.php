<?php

namespace Modules\NurDu\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\NurDu\Models\ActionItem;
use Modules\NurDu\Models\VisionCheck;

class VisionCheckController extends Controller
{
    public function index(Request $request): View
    {
        $checks = VisionCheck::where('team_id', $request->user()->current_team_id)
            ->with('actionItems')
            ->orderByDesc('check_date')
            ->paginate(12);

        return view('nurdu::checks.index', compact('checks'));
    }

    public function create(): View
    {
        return view('nurdu::checks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'check_date' => ['required', 'date'],
            'q1_answer' => ['required', 'in:yes,partially,no'],
            'q2_answer' => ['nullable', 'string', 'max:2000'],
            'q3_answer' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'action_items' => ['nullable', 'array'],
            'action_items.*' => ['string', 'max:255'],
        ]);

        $user = $request->user();

        $check = VisionCheck::create([
            'team_id' => $user->current_team_id,
            'user_id' => $user->id,
        ] + $validated);

        if ($request->has('action_items')) {
            foreach (array_filter($request->action_items) as $item) {
                $check->actionItems()->create(['title' => $item]);
            }
        }

        return redirect()->route('nurdu.checks.index')->with('success', __('Vision check recorded.'));
    }

    public function show(Request $request, VisionCheck $check): View
    {
        abort_if($check->team_id !== $request->user()->current_team_id, 403);
        $check->load('actionItems');

        return view('nurdu::checks.show', compact('check'));
    }

    public function toggleActionItem(Request $request, ActionItem $actionItem): RedirectResponse
    {
        $check = $actionItem->visionCheck;
        abort_if($check->team_id !== $request->user()->current_team_id, 403);

        $actionItem->update(['completed' => ! $actionItem->completed]);

        return redirect()->back()->with('success', __('Action item updated.'));
    }

    public function destroy(Request $request, VisionCheck $check): RedirectResponse
    {
        abort_if($check->team_id !== $request->user()->current_team_id, 403);
        $check->delete();

        return redirect()->route('nurdu.checks.index')->with('success', __('Vision check deleted.'));
    }
}
