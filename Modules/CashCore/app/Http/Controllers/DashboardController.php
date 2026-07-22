<?php

namespace Modules\CashCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\CashCore\Models\CashCategory;
use Modules\CashCore\Services\CashCoreService;

class DashboardController extends Controller
{
    public function __construct(private CashCoreService $service) {}

    public function index(Request $request): View
    {
        $team = $request->user()?->currentTeam;
        $period = $request->get('period', now()->format('Y-m'));

        CashCategory::getDefaults($team?->id, auth()->id());

        $data = $this->service->dashboardData($team?->id, $period);

        return view('cashcore::dashboard.index', $data);
    }
}
