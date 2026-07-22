<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Models\ScanEvent;

class ScanEventController extends Controller
{
    public function index(Request $request): View
    {
        $query = ScanEvent::with(['order', 'workstation', 'user'])->orderByDesc('scanned_at');

        if ($request->order_id) {
            $query->where('dentaltrack_order_id', $request->order_id);
        }

        if ($request->workstation_id) {
            $query->where('dentaltrack_workstation_id', $request->workstation_id);
        }

        $events = $query->paginate(50)->withQueryString();

        return view('dentaltrack::admin.scan-events.index', compact('events'));
    }
}
