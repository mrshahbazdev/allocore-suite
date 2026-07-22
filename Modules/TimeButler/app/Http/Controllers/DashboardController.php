<?php

namespace Modules\TimeButler\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\TimeButler\Models\AbsenceRequest;
use Modules\TimeButler\Models\AbsenceType;
use Modules\TimeButler\Models\TimeEntry;
use Modules\TimeButler\Services\VacationBalanceService;

class DashboardController extends Controller
{
    public function index(VacationBalanceService $balanceService): View
    {
        $user = auth()->user();
        $team = $user->currentTeam;
        $year = now()->year;

        if ($team && AbsenceType::query()->where('team_id', $team->id)->doesntExist()) {
            $balanceService->seedDefaultTypes($team->id, $user->id);
        }

        $balance = $team ? $balanceService->recalculate($user, $team, $year) : null;

        $pendingRequests = AbsenceRequest::query()
            ->where('status', 'pending')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $teamPending = AbsenceRequest::query()
            ->where('status', 'pending')
            ->where('user_id', '!=', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $openTimeEntry = TimeEntry::query()
            ->where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        return view('timebutler::dashboard.index', compact('balance', 'pendingRequests', 'teamPending', 'openTimeEntry'));
    }
}
