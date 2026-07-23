<?php

namespace App\Support;

use App\Models\Module;
use Illuminate\Support\Collection;

class ModuleMarketplace
{
    protected array $metadata = [
        'invoice-maker' => ['category' => 'Finance', 'features' => ['Invoices', 'Estimates', 'Payments', 'PDF export']],
        'audit' => ['category' => 'Governance', 'features' => ['Maturity audits', 'Templates', 'PDF reports']],
        'keyword-cluster' => ['category' => 'Marketing', 'features' => ['Keyword clustering', 'CSV import/export', 'Public share']],
        'lead-quality' => ['category' => 'Sales', 'features' => ['Lead scoring', 'Outreach sequences', 'CRM pipeline']],
        'time-butler' => ['category' => 'People', 'features' => ['Absence requests', 'Team calendar', 'Clock in/out']],
        'plan-hive' => ['category' => 'Productivity', 'features' => ['Projects', 'Tasks', 'Calendar', 'Documents']],
        'kpi-tool' => ['category' => 'Analytics', 'features' => ['KPI catalog', 'Monthly targets', 'Spreadsheet view']],
        'loop-engine' => ['category' => 'Operations', 'features' => ['Decision SOPs', 'Process runs', 'Templates']],
        'smart-kpi' => ['category' => 'Analytics', 'features' => ['Hierarchical KPIs', 'Forecasts', 'Problems & actions']],
        'cash-core' => ['category' => 'Finance', 'features' => ['Cashflow', 'Profit First', 'Expense tracking']],
        'bunny-band' => ['category' => 'Community', 'features' => ['Rewards', 'Referrals', 'Levels']],
        'dental-track' => ['category' => 'Manufacturing', 'features' => ['QR tracking', 'Production board', 'Predictions']],
        'focus-matrix' => ['category' => 'Productivity', 'features' => ['Triage', 'Delegation', 'Manager tools']],
        'org-matrix' => ['category' => 'People', 'features' => ['Org chart', 'Roles', 'People directory']],
        'vision-flow' => ['category' => 'Strategy', 'features' => ['Values', 'Vision', 'Strategic goals']],
        'nur-du' => ['category' => 'Strategy', 'features' => ['Quarterly focus', 'Decision log', 'Vision check']],
        'financial-platform' => ['category' => 'Finance', 'features' => ['Deep KPIs', 'Revenue sync', 'Bank import']],
        'sweet-spot' => ['category' => 'Sales', 'features' => ['Customer scoring', 'Profitability', 'Weights']],
    ];

    public function all(): Collection
    {
        return Module::where('is_active', true)->orderBy('name')->get()->map(function (Module $module) {
            $meta = $this->metadata[$module->key] ?? ['category' => 'Tools', 'features' => []];

            return array_merge($module->toArray(), $meta);
        });
    }

    public function grouped(): Collection
    {
        return $this->all()->groupBy('category')->sortKeys();
    }

    public function forModule(string $key): ?array
    {
        $module = Module::where('key', $key)->where('is_active', true)->first();

        if (! $module) {
            return null;
        }

        $meta = $this->metadata[$module->key] ?? ['category' => 'Tools', 'features' => []];

        return array_merge($module->toArray(), $meta);
    }
}
