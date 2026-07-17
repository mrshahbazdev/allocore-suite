<?php

namespace App\Http\Controllers;

use App\Models\StatusIncident;
use Illuminate\View\View;

class StatusPageController extends Controller
{
    public function index(): View
    {
        $active = StatusIncident::active()->latest('started_at')->get();
        $resolved = StatusIncident::resolved()->recent()->get();

        $overallStatus = $active->isEmpty() ? 'operational' : ($active->contains('severity', 'critical') ? 'major' : 'degraded');

        return view('status.index', compact('active', 'resolved', 'overallStatus'));
    }
}
