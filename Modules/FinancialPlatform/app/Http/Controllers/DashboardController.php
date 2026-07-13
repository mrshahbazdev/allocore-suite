<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\FinancialPlatform\Models\Analysis;
use Modules\FinancialPlatform\Models\Company;
use Modules\FinancialPlatform\Models\Lead;
use Modules\FinancialPlatform\Models\PaypalTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'companies' => Company::query()->count(),
            'gmbh' => Analysis::query()->where('type', 'gmbh')->count(),
            'jahresabschluss' => Analysis::query()->where('type', 'jahresabschluss')->count(),
            'immobilien' => Analysis::query()->where('type', 'immobilien')->count(),
            'leads' => Lead::query()->count(),
            'paypal_revenue' => PaypalTransaction::query()->where('status', 'completed')->sum('amount'),
        ];

        $recentAnalyses = Analysis::query()
            ->with('company')
            ->whereNotNull('total_score')
            ->latest()
            ->take(8)
            ->get();

        $companies = Company::query()
            ->withCount('analyses')
            ->latest()
            ->take(5)
            ->get();

        return view('financialplatform::dashboard', compact('stats', 'recentAnalyses', 'companies'));
    }
}
