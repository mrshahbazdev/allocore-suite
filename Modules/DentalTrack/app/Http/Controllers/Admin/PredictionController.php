<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\DentalTrack\Services\PredictionService;

class PredictionController extends Controller
{
    public function __construct(
        private readonly PredictionService $predictionService,
    ) {}

    public function index(): View
    {
        $stats = $this->predictionService->getAccuracyStats();
        $suggestions = $this->predictionService->getSmartSuggestions();

        return view('dentaltrack::admin.predictions.index', compact('stats', 'suggestions'));
    }
}
