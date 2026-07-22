<?php

namespace Modules\TimeButler\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\TimeButler\Models\AbsenceRequest;
use Modules\TimeButler\Models\Holiday;

class TeamCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $team = auth()->user()->currentTeam;
        $users = User::query()
            ->where('current_team_id', $team->id)
            ->orWhereHas('teams', fn ($q) => $q->where('teams.id', $team->id))
            ->orderBy('name')
            ->get();

        $requests = AbsenceRequest::query()
            ->with('absenceType')
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($start, $end): void {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end): void {
                        $q->where('start_date', '<=', $start)->where('end_date', '>=', $end);
                    });
            })
            ->get();

        $holidays = Holiday::query()
            ->whereYear('date', $year)
            ->get()
            ->keyBy(fn ($holiday) => $holiday->date->format('Y-m-d'));

        $period = CarbonPeriod::create($start, $end);

        return view('timebutler::calendar.index', compact('users', 'requests', 'holidays', 'period', 'year', 'month', 'start'));
    }
}
