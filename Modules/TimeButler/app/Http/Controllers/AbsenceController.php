<?php

namespace Modules\TimeButler\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\TimeButler\Models\AbsenceRequest;
use Modules\TimeButler\Models\AbsenceType;
use Modules\TimeButler\Models\Holiday;
use Modules\TimeButler\Services\VacationBalanceService;

class AbsenceController extends Controller
{
    public function index(Request $request): View
    {
        $query = AbsenceRequest::query()->with(['user', 'absenceType'])->latest('start_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('absence_type_id', $request->type);
        }

        return view('timebutler::absences.index', [
            'requests' => $query->paginate(20)->withQueryString(),
            'types' => AbsenceType::query()->where('is_active', true)->get(),
        ]);
    }

    public function create(): View
    {
        return view('timebutler::absences.form', [
            'types' => AbsenceType::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'users' => User::query()->where('current_team_id', auth()->user()->current_team_id)->get(),
            'request' => new AbsenceRequest,
        ]);
    }

    public function store(Request $request, VacationBalanceService $balanceService): RedirectResponse
    {
        $validated = $request->validate([
            'absence_type_id' => 'required|exists:timebutler_absence_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'half_day_start' => 'nullable|boolean',
            'half_day_end' => 'nullable|boolean',
            'substitute_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $type = AbsenceType::query()->findOrFail($validated['absence_type_id']);
        $team = auth()->user()->currentTeam;

        $holidays = Holiday::query()
            ->where('team_id', $team->id)
            ->whereYear('date', now()->year)
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();

        $days = VacationBalanceService::calculateDays(
            $validated['start_date'],
            $validated['end_date'],
            $request->boolean('half_day_start'),
            $request->boolean('half_day_end'),
            fn ($date) => in_array($date->format('Y-m-d'), $holidays, true)
        );

        $absence = AbsenceRequest::create([
            'team_id' => $team->id,
            'user_id' => auth()->id(),
            'absence_type_id' => $type->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'half_day_start' => $request->boolean('half_day_start'),
            'half_day_end' => $request->boolean('half_day_end'),
            'total_days' => $days,
            'status' => $type->requires_approval ? 'pending' : 'approved',
            'substitute_id' => $validated['substitute_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'approved_at' => $type->requires_approval ? null : now(),
            'approved_by' => $type->requires_approval ? null : auth()->id(),
        ]);

        $this->syncAbsenceDays($absence);

        if ($type->deducts_vacation) {
            $balanceService->recalculate(auth()->user(), $team, now()->year);
        }

        return redirect()->route('timebutler.absences.index')->with('success', __('Absence request saved.'));
    }

    public function show(AbsenceRequest $absence): View
    {
        $absence->load(['user', 'absenceType', 'substitute', 'approver']);

        return view('timebutler::absences.show', compact('absence'));
    }

    public function approve(AbsenceRequest $absence, VacationBalanceService $balanceService): RedirectResponse
    {
        $absence->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $balanceService->recalculate($absence->user, $absence->team, $absence->start_date->year);

        return back()->with('success', __('Absence approved.'));
    }

    public function reject(Request $request, AbsenceRequest $absence, VacationBalanceService $balanceService): RedirectResponse
    {
        $validated = $request->validate(['rejection_reason' => 'required|string']);

        $absence->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $balanceService->recalculate($absence->user, $absence->team, $absence->start_date->year);

        return back()->with('success', __('Absence rejected.'));
    }

    public function cancel(AbsenceRequest $absence, VacationBalanceService $balanceService): RedirectResponse
    {
        if ($absence->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return back()->with('error', __('Not authorized.'));
        }

        $absence->update(['status' => 'cancelled']);

        $balanceService->recalculate($absence->user, $absence->team, $absence->start_date->year);

        return back()->with('success', __('Absence cancelled.'));
    }

    public function destroy(AbsenceRequest $absence, VacationBalanceService $balanceService): RedirectResponse
    {
        if ($absence->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return back()->with('error', __('Not authorized.'));
        }

        $year = $absence->start_date->year;
        $user = $absence->user;
        $team = $absence->team;

        $absence->delete();

        $balanceService->recalculate($user, $team, $year);

        return redirect()->route('timebutler.absences.index')->with('success', __('Absence deleted.'));
    }

    private function syncAbsenceDays(AbsenceRequest $absence): void
    {
        $absence->absenceDays()->delete();
        $period = CarbonPeriod::create($absence->start_date, $absence->end_date);

        foreach ($period as $date) {
            $isFirst = $date->format('Y-m-d') === $absence->start_date->format('Y-m-d');
            $isLast = $date->format('Y-m-d') === $absence->end_date->format('Y-m-d');
            $halfDay = ($isFirst && $absence->half_day_start) || ($isLast && $absence->half_day_end);

            $absence->absenceDays()->create([
                'date' => $date,
                'half_day' => $halfDay,
            ]);
        }
    }
}
