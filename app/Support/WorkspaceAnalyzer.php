<?php

namespace App\Support;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class WorkspaceAnalyzer
{
    public function __construct(protected ModuleStats $moduleStats) {}

    protected array $titleColumns = [
        'name', 'title', 'name_en', 'name_de', 'statement', 'description',
        'invoice_number', 'patient_ref', 'doctor_name', 'question', 'vision',
        'project_name', 'company_name', 'first_name', 'email', 'id',
    ];

    protected array $connections = [
        [
            'title_key' => 'workspace.revenue_snapshot',
            'desc_key' => 'workspace.revenue_snapshot_desc',
            'modules' => ['invoice-maker', 'cash-core'],
            'action' => 'app/cashcore/dashboard',
        ],
        [
            'title_key' => 'workspace.lead_to_invoice',
            'desc_key' => 'workspace.lead_to_invoice_desc',
            'modules' => ['lead-quality', 'invoice-maker'],
            'action' => 'app/leads/contacts',
        ],
        [
            'title_key' => 'workspace.project_focus',
            'desc_key' => 'workspace.project_focus_desc',
            'modules' => ['plan-hive', 'focus-matrix'],
            'action' => 'app/planhive/projects',
        ],
        [
            'title_key' => 'workspace.audit_to_kpi',
            'desc_key' => 'workspace.audit_to_kpi_desc',
            'modules' => ['audit', 'kpi-tool'],
            'action' => 'app/audit/audits',
        ],
        [
            'title_key' => 'workspace.financial_kpis',
            'desc_key' => 'workspace.financial_kpis_desc',
            'modules' => ['smart-kpi', 'financial-platform'],
            'action' => 'app/finance/companies',
        ],
        [
            'title_key' => 'workspace.team_capacity',
            'desc_key' => 'workspace.team_capacity_desc',
            'modules' => ['time-butler', 'plan-hive'],
            'action' => 'app/timebutler',
        ],
        [
            'title_key' => 'workspace.content_outreach',
            'desc_key' => 'workspace.content_outreach_desc',
            'modules' => ['keyword-cluster', 'lead-quality'],
            'action' => 'app/clusters/projects',
        ],
        [
            'title_key' => 'workspace.lab_revenue',
            'desc_key' => 'workspace.lab_revenue_desc',
            'modules' => ['dental-track', 'cash-core'],
            'action' => 'app/dentaltrack/orders',
        ],
        [
            'title_key' => 'workspace.sops_in_projects',
            'desc_key' => 'workspace.sops_in_projects_desc',
            'modules' => ['loop-engine', 'plan-hive'],
            'action' => 'app/loopengine/processes',
        ],
        [
            'title_key' => 'workspace.vision_alignment',
            'desc_key' => 'workspace.vision_alignment_desc',
            'modules' => ['nur-du', 'vision-flow'],
            'action' => 'app/nurdu',
        ],
        [
            'title_key' => 'workspace.referral_rewards',
            'desc_key' => 'workspace.referral_rewards_desc',
            'modules' => ['bunny-band', 'lead-quality'],
            'action' => 'app/bunnyband/dashboard',
        ],
        [
            'title_key' => 'workspace.org_projects',
            'desc_key' => 'workspace.org_projects_desc',
            'modules' => ['org-matrix', 'plan-hive'],
            'action' => 'app/orgmatrix/organizations',
        ],
    ];

    public function analyze(User $user): array
    {
        $modules = Module::where('is_active', true)->orderBy('name')->get();
        $moduleItems = [];

        foreach ($modules as $module) {
            if (! $user->hasModule($module->key)) {
                continue;
            }

            $stat = $this->moduleStats->forModule($user, $module);

            $moduleItems[] = [
                'key' => $module->key,
                'name' => $module->name,
                'route_prefix' => $module->route_prefix,
                'count' => $stat['primary_resource_count'] ?? 0,
                'label' => $stat['primary_resource'] ?? null,
                'recent' => $this->recentRecords($module->key, $module->route_prefix, $user),
                'setup_link' => url('app/'.$module->route_prefix),
            ];
        }

        $onboarding = $this->onboardingSteps($moduleItems);

        return [
            'modules' => $moduleItems,
            'onboarding' => $onboarding,
            'insights' => $this->crossToolInsights($user, $moduleItems),
            'next_step' => collect($onboarding)->firstWhere('complete', false),
        ];
    }

    protected function recentRecords(string $moduleKey, string $routePrefix, User $user): array
    {
        $modelClass = $this->moduleStats->modelFor($moduleKey);

        if (! $modelClass || ! class_exists($modelClass)) {
            return [];
        }

        $model = new $modelClass;
        $query = $modelClass::query();

        if ($user->current_team_id && Schema::hasColumn($model->getTable(), 'team_id')) {
            $query->where('team_id', $user->current_team_id);
        }

        $records = $query->latest()->take(5)->get();

        return $records->map(fn ($record) => [
            'id' => $record->getKey(),
            'title' => $this->titleFor($record),
            'url' => url('app/'.$routePrefix),
            'created_at' => $record->created_at?->diffForHumans(),
        ])->all();
    }

    protected function titleFor($record): string
    {
        foreach ($this->titleColumns as $column) {
            $value = $record->getAttribute($column);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return '#'.$record->getKey();
    }

    protected function onboardingSteps(array $modules): array
    {
        return collect($modules)->map(function ($module) {
            $complete = $module['count'] > 0;

            return [
                'module' => $module['name'],
                'label' => __('Create your first :label', ['label' => mb_strtolower($module['label'] ?? 'record')]),
                'complete' => $complete,
                'link' => $module['setup_link'],
                'count' => $module['count'],
            ];
        })->all();
    }

    protected function crossToolInsights(User $user, array $modules): array
    {
        $byKey = collect($modules)->keyBy('key');

        return collect($this->connections)->map(function ($connection) use ($user, $byKey) {
            $moduleKeys = $connection['modules'];
            $accessible = collect($moduleKeys)->every(fn ($key) => $user->hasModule($key));
            $counts = collect($moduleKeys)->map(fn ($key) => $byKey->get($key)['count'] ?? 0);
            $hasData = $counts->every(fn ($count) => $count > 0);

            return [
                'title' => __($connection['title_key']),
                'description' => __($connection['desc_key']),
                'modules' => $moduleKeys,
                'accessible' => $accessible,
                'unlocked' => $accessible && $hasData,
                'counts' => $counts->all(),
                'action' => $accessible ? url($connection['action']) : route('tools.index'),
                'missing_module' => $accessible ? null : collect($moduleKeys)->first(fn ($key) => ! $user->hasModule($key)),
            ];
        })->all();
    }
}
