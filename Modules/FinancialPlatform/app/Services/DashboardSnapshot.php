<?php

namespace Modules\FinancialPlatform\Services;

use App\Models\Team;
use Modules\FinancialPlatform\Models\Analysis;
use Modules\FinancialPlatform\Models\Company;
use Modules\FinancialPlatform\Models\Lead;
use Modules\FinancialPlatform\Models\PaypalTransaction;

class DashboardSnapshot
{
    public function __construct(
        private readonly DeepKpiSnapshot $deepKpiSnapshot,
    ) {}

    public function forTeam(?Team $team): array
    {
        if (! $team) {
            return [
                'companies' => 0,
                'analyses' => 0,
                'leads' => 0,
                'revenue' => 0,
                'maturity' => 0,
                'revenueDevelopment' => app(RevenueDevelopmentSnapshot::class)->forTeam(null),
                'deepKpis' => $this->deepKpiSnapshot->forTeam(null),
                'recentAnalyses' => collect(),
            ];
        }

        $analyses = Analysis::query()->where('team_id', $team->id);

        return [
            'companies' => Company::query()->where('team_id', $team->id)->count(),
            'analyses' => $analyses->count(),
            'leads' => Lead::query()->where('team_id', $team->id)->count(),
            'revenue' => PaypalTransaction::query()
                ->where('team_id', $team->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'maturity' => (float) round((float) ($analyses->avg('total_score') ?? 0), 1),
            'revenueDevelopment' => app(RevenueDevelopmentSnapshot::class)->forTeam($team),
            'deepKpis' => $this->deepKpiSnapshot->forTeam($team),
            'recentAnalyses' => Analysis::query()
                ->where('team_id', $team->id)
                ->with('company')
                ->latest()
                ->take(5)
                ->get(),
        ];
    }
}
