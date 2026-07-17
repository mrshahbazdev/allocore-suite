<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Module;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardWidgetRegistry $registry)
    {
        $user = $request->user();
        $modules = Module::where('is_active', true)->get();
        $accessible = $user->accessibleModules()->pluck('key')->all();
        $widgets = $registry->forUser($user);
        $announcements = Announcement::active()->latest()->take(3)->get();

        return view('dashboard', compact('modules', 'accessible', 'widgets', 'announcements'));
    }
}
