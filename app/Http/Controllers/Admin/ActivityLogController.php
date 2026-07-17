<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with(['causer', 'subject', 'team'])
            ->when($request->filled('log_name'), function ($query) use ($request) {
                $query->where('log_name', $request->log_name);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('description', 'like', '%'.$request->search.'%');
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $logNames = ActivityLog::distinct()->orderBy('log_name')->pluck('log_name');

        return view('admin.activity-logs.index', compact('logs', 'logNames'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load(['causer', 'subject', 'team']);

        return view('admin.activity-logs.show', compact('activityLog'));
    }
}
