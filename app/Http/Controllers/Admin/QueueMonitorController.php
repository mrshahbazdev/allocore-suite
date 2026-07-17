<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class QueueMonitorController extends Controller
{
    public function index()
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->paginate(20);

        $counts = [
            'failed' => DB::table('failed_jobs')->count(),
            'pending' => DB::table('jobs')->count(),
            'delayed' => DB::table('job_batches')->count(),
        ];

        return view('admin.queue-monitor.index', compact('failedJobs', 'counts'));
    }
}
