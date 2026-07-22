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

        $logs = ActivityLog::with(['subject', 'causer'])
            ->where(function ($query) use ($user) {
                $query->where('team_id', $user->current_team_id)
                    ->orWhere('causer_id', $user->id);
            })
            ->latest()
            ->paginate(25);

        return view('timeline.index', compact('logs'));
    }
}
