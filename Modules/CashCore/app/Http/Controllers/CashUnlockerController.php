<?php

namespace Modules\CashCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\CashCore\Models\LiquidityBlocker;

class CashUnlockerController extends Controller
{
    public function index(): View
    {
        $activeBlockers = LiquidityBlocker::active()->orderByDesc('blocked_amount')->get();
        $resolvedBlockers = LiquidityBlocker::where('status', 'resolved')->latest()->get();
        $totalBlocked = $activeBlockers->sum('blocked_amount');

        return view('cashcore::unlocker.index', compact('activeBlockers', 'resolvedBlockers', 'totalBlocked'));
    }

    public function create(): View
    {
        return view('cashcore::unlocker.form', ['blocker' => new LiquidityBlocker]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        LiquidityBlocker::create($validated);

        return redirect()->route('cashcore.unlocker.index')->with('success', __('Blocker created.'));
    }

    public function edit(LiquidityBlocker $blocker): View
    {
        return view('cashcore::unlocker.form', compact('blocker'));
    }

    public function update(Request $request, LiquidityBlocker $blocker): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $blocker->update($validated);

        return redirect()->route('cashcore.unlocker.index')->with('success', __('Blocker updated.'));
    }

    public function destroy(LiquidityBlocker $blocker): RedirectResponse
    {
        $blocker->delete();

        return redirect()->route('cashcore.unlocker.index')->with('success', __('Blocker deleted.'));
    }

    public function updateStatus(Request $request, LiquidityBlocker $blocker): RedirectResponse
    {
        $request->validate(['status' => 'required|in:active,in_progress,resolved']);
        $blocker->update(['status' => $request->status]);

        return back()->with('success', __('Blocker updated.'));
    }

    private function rules(): array
    {
        return [
            'blocker_type' => 'required|in:open_invoice,payment_terms,inventory,inefficient_flow',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'blocked_amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'debtor_name' => 'nullable|string|max:255',
            'days_overdue' => 'integer|min:0',
            'status' => 'nullable|in:active,in_progress,resolved',
            'action_items' => 'nullable|array',
        ];
    }
}
