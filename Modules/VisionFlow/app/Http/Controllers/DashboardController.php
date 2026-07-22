<?php

namespace Modules\VisionFlow\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\VisionFlow\Models\Organization;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $organizations = Organization::where('team_id', $request->user()->current_team_id)
            ->withCount(['values', 'principles', 'strategicGoals', 'missions', 'projects'])
            ->latest()
            ->get();

        return view('visionflow::dashboard.index', compact('organizations'));
    }
}
