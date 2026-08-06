<?php

namespace Modules\AuditIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\AuditIntelligence\Models\Finding;
use Modules\AuditIntelligence\Models\Recommendation;
use Modules\AuditIntelligence\Models\Upsell;

class DashboardController extends Controller
{
    public function index()
    {
        return view('auditintelligence::dashboard', [
            'findings' => Finding::latest()->take(5)->get(),
            'stats' => [
                'findings' => Finding::count(),
                'recommendations' => Recommendation::count(),
                'upsells' => Upsell::count(),
                'critical' => Finding::where('risk_level', 'critical')->count(),
            ],
        ]);
    }
}
