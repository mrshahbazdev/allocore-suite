<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;
use Modules\DentalTrack\Enums\ScanEventType;
use Modules\DentalTrack\Models\ScanEvent;

class EmployeePerformanceController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->limit(200)->get();

        $performance = $users->map(function ($user) {
            $completeEvents = ScanEvent::where('user_id', $user->id)
                ->where('event_type', ScanEventType::Complete)
                ->whereNotNull('duration_seconds')
                ->get();

            $avgSeconds = $completeEvents->avg('duration_seconds');
            $totalSeconds = $completeEvents->sum('duration_seconds');

            return [
                'user' => $user,
                'orders_completed' => $completeEvents->unique('dentaltrack_order_id')->count(),
                'steps_completed' => $completeEvents->count(),
                'avg_minutes' => $avgSeconds !== null ? round($avgSeconds / 60, 1) : 0,
                'total_hours' => $totalSeconds ? round($totalSeconds / 3600, 1) : 0,
            ];
        })->sortByDesc('steps_completed')->values();

        return view('dentaltrack::admin.employee-performance.index', compact('performance'));
    }
}
