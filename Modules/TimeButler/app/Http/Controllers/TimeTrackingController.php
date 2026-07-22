<?php

namespace Modules\TimeButler\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\TimeButler\Models\TimeEntry;

class TimeTrackingController extends Controller
{
    public function index(Request $request): View
    {
        $query = TimeEntry::query()->where('user_id', auth()->id())->latest('date');

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        return view('timebutler::time-tracking.index', [
            'entries' => $query->paginate(25)->withQueryString(),
            'openEntry' => TimeEntry::query()->where('user_id', auth()->id())->whereNull('end_time')->first(),
        ]);
    }

    public function clockIn(): RedirectResponse
    {
        $open = TimeEntry::query()->where('user_id', auth()->id())->whereNull('end_time')->first();

        if ($open) {
            return back()->with('warning', __('Already clocked in.'));
        }

        TimeEntry::create([
            'team_id' => auth()->user()->current_team_id,
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'start_time' => now()->format('H:i'),
            'end_time' => null,
            'break_minutes' => 0,
        ]);

        return back()->with('success', __('Clocked in.'));
    }

    public function clockOut(): RedirectResponse
    {
        $open = TimeEntry::query()->where('user_id', auth()->id())->whereNull('end_time')->first();

        if (! $open) {
            return back()->with('warning', __('No open time entry.'));
        }

        $open->update(['end_time' => now()->format('H:i')]);

        return back()->with('success', __('Clocked out.'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'break_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        TimeEntry::create([
            'team_id' => auth()->user()->current_team_id,
            'user_id' => auth()->id(),
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'break_minutes' => $validated['break_minutes'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', __('Time entry added.'));
    }

    public function destroy(TimeEntry $timeEntry): RedirectResponse
    {
        if ($timeEntry->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return back()->with('error', __('Not authorized.'));
        }

        $timeEntry->delete();

        return back()->with('success', __('Time entry deleted.'));
    }
}
