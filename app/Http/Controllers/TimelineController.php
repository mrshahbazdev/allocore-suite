<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $team = $user->currentTeam;

        $logs = ActivityLog::with(['subject', 'causer'])
            ->where(function ($query) use ($user) {
                $query->where('team_id', $user->current_team_id)
                    ->orWhere('causer_id', $user->id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('description', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('log_name'), function ($query) use ($request) {
                $query->where('log_name', $request->log_name);
            })
            ->when($request->filled('causer_id'), function ($query) use ($request) {
                $query->where('causer_id', $request->causer_id);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $logNames = ActivityLog::distinct()->orderBy('log_name')->pluck('log_name');
        $members = $team ? $team->members()->orderBy('name')->get(['users.id', 'users.name']) : collect();

        return view('timeline.index', compact('logs', 'logNames', 'members'));
    }
}
