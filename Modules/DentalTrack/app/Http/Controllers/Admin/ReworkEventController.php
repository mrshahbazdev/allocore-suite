<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Enums\ReworkStatus;
use Modules\DentalTrack\Models\OrderStep;
use Modules\DentalTrack\Models\ReworkEvent;

class ReworkEventController extends Controller
{
    public function index(): View
    {
        $events = ReworkEvent::with(['order', 'orderStep', 'flaggedByUser'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dentaltrack::admin.rework-events.index', compact('events'));
    }

    public function create(): View
    {
        $steps = OrderStep::with('order')->whereIn('status', ['done', 'in_progress'])->get();

        return view('dentaltrack::admin.rework-events.form', compact('steps'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $step = OrderStep::findOrFail($validated['dentaltrack_order_step_id']);

        ReworkEvent::create([
            'dentaltrack_order_id' => $step->dentaltrack_order_id,
            'dentaltrack_order_step_id' => $step->id,
            'flagged_by' => auth()->id(),
            'original_technician' => $step->assigned_to,
            'cause' => $validated['cause'],
            'description' => $validated['description'] ?? null,
        ]);

        $step->update(['status' => 'pending']);

        return redirect()->route('dentaltrack.admin.rework-events.index')->with('success', __('Rework flagged.'));
    }

    public function resolve(ReworkEvent $reworkEvent): RedirectResponse
    {
        $reworkEvent->update([
            'status' => ReworkStatus::Resolved,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return back()->with('success', __('Rework resolved.'));
    }

    private function rules(): array
    {
        return [
            'dentaltrack_order_step_id' => 'required|exists:dentaltrack_order_steps,id',
            'cause' => 'required|in:material_defect,technique_error,equipment_issue,design_error,other',
            'description' => 'nullable|string',
        ];
    }
}
