<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\DentalTrack\Models\ReworkEvent;

class QualityController extends Controller
{
    public function index(): View
    {
        $open = ReworkEvent::where('status', '!=', 'resolved')->count();
        $byCause = ReworkEvent::selectRaw('cause, COUNT(*) as total')
            ->groupBy('cause')
            ->pluck('total', 'cause')
            ->toArray();

        $recent = ReworkEvent::with(['order', 'orderStep', 'flaggedByUser'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('dentaltrack::admin.quality.index', compact('open', 'byCause', 'recent'));
    }
}
