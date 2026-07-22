<?php

namespace Modules\TimeButler\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\TimeButler\Models\AbsenceRequest;
use Modules\TimeButler\Models\TimeEntry;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function absence(Request $request): View|Response
    {
        $start = Carbon::parse($request->get('start', now()->startOfMonth()->toDateString()));
        $end = Carbon::parse($request->get('end', now()->endOfMonth()->toDateString()));
        $userId = $request->get('user_id');

        $query = AbsenceRequest::query()
            ->with(['user', 'absenceType'])
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->when($userId, fn ($q) => $q->where('user_id', $userId));

        $items = $request->get('format') === 'pdf' ? $query->get() : $query->paginate(25)->withQueryString();

        $summary = collect($items->items ?? $items)->groupBy(fn ($req) => $req->absenceType->name ?? '-')
            ->map(fn ($group) => [
                'type' => $group->first()->absenceType->name ?? '-',
                'days' => $group->sum('total_days'),
                'count' => $group->count(),
            ]);

        if ($request->get('format') === 'pdf') {
            return Pdf::loadView('timebutler::reports.absence-pdf', compact('items', 'summary', 'start', 'end'))
                ->download('absence-report.pdf');
        }

        $users = User::query()
            ->where('current_team_id', auth()->user()->current_team_id)
            ->orWhereHas('teams', fn ($q) => $q->where('teams.id', auth()->user()->current_team_id))
            ->orderBy('name')
            ->get();

        return view('timebutler::reports.absence', compact('items', 'summary', 'users', 'start', 'end'));
    }

    public function time(Request $request): View|Response
    {
        $start = Carbon::parse($request->get('start', now()->startOfMonth()->toDateString()));
        $end = Carbon::parse($request->get('end', now()->endOfMonth()->toDateString()));
        $userId = $request->get('user_id');

        $query = TimeEntry::query()
            ->whereBetween('date', [$start, $end])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->with('user');

        $items = $request->get('format') === 'pdf' ? $query->get() : $query->paginate(25)->withQueryString();

        $totalMinutes = collect($items->items ?? $items)->sum(fn ($entry) => $entry->durationMinutes() ?? 0);

        if ($request->get('format') === 'pdf') {
            return Pdf::loadView('timebutler::reports.time-pdf', compact('items', 'totalMinutes', 'start', 'end'))
                ->download('time-report.pdf');
        }

        $users = User::query()
            ->where('current_team_id', auth()->user()->current_team_id)
            ->orWhereHas('teams', fn ($q) => $q->where('teams.id', auth()->user()->current_team_id))
            ->orderBy('name')
            ->get();

        return view('timebutler::reports.time', compact('items', 'users', 'totalMinutes', 'start', 'end'));
    }
}
