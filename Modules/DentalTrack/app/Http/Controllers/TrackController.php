<?php

namespace Modules\DentalTrack\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Models\Order;

class TrackController extends Controller
{
    public function __invoke(Request $request): View
    {
        $code = $request->input('code');
        $order = null;

        if ($code) {
            $order = Order::where('tracking_code', $code)
                ->with(['productType', 'steps', 'lab', 'scanEvents.workstation', 'predictions'])
                ->first();
        }

        return view('dentaltrack::track.index', compact('order', 'code'));
    }
}
