<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $mode = (bool) SiteSetting::value('maintenance_mode');
        $message = SiteSetting::value('maintenance_message') ?: __('Service temporarily unavailable.');

        return view('admin.maintenance.index', compact('mode', 'message'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'maintenance_mode' => 'boolean',
            'maintenance_message' => 'nullable|string|max:1000',
        ]);

        SiteSetting::set('maintenance_mode', $validated['maintenance_mode'] ? '1' : '');
        SiteSetting::set('maintenance_message', $validated['maintenance_message'] ?? '');

        return back()->with('success', __('admin.maintenance.updated'));
    }
}
