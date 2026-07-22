<?php

namespace App\Support;

use App\Models\Module;
use App\Models\User;

class AiAdvisor
{
    public function __construct(protected ModuleStats $moduleStats, protected WorkspaceAnalyzer $workspace) {}

    public function forUser(User $user): array
    {
        $modules = collect($this->moduleStats->forUser($user))
            ->filter(fn ($stat) => $stat['accessible'])
            ->all();

        $recommendations = [];

        foreach ($modules as $key => $stat) {
            $module = Module::where('key', $key)->first();
            if (! $module) {
                continue;
            }

            $route = url('app/'.$module->route_prefix);

            if ($stat['count'] === 0 || $stat['count'] === null) {
                $recommendations[] = $this->tip(
                    'create_first',
                    __('Create your first :label in :module', [
                        'label' => mb_strtolower($stat['label'] ?? 'record'),
                        'module' => $module->name,
                    ]),
                    __('Start collecting data so :module can begin showing insights.', ['module' => $module->name]),
                    $route,
                    $key
                );
            } elseif ($stat['count'] < 5) {
                $recommendations[] = $this->tip(
                    'add_more',
                    __('Add more :label to :module', [
                        'label' => mb_strtolower($stat['label'] ?? 'records'),
                        'module' => $module->name,
                    ]),
                    __('You have :count records; more data improves reports and forecasts.', ['count' => $stat['count']]),
                    $route,
                    $key
                );
            }
        }

        $recommendations = array_merge($recommendations, $this->crossToolRecommendations($modules));

        return collect($recommendations)
            ->sortByDesc(fn ($r) => $r['priority'])
            ->values()
            ->all();
    }

    protected function crossToolRecommendations(array $modules): array
    {
        $recommendations = [];
        $counts = collect($modules)->mapWithKeys(fn ($stat, $key) => [$key => $stat['count'] ?? 0])->all();

        $suggest = function (string $whenKey, string $needKey, string $titleKey, string $desc, string $url) use ($counts, &$recommendations) {
            if (($counts[$whenKey] ?? 0) > 0 && ($counts[$needKey] ?? 0) === 0) {
                $recommendations[] = $this->tip('cross_tool', __($titleKey), $desc, $url, $needKey, 2);
            }
        };

        $suggest('invoice-maker', 'cash-core', 'advisor.link_cash', __('Record cash transactions to see revenue collected from your invoices.'), url('app/cashcore'));
        $suggest('lead-quality', 'invoice-maker', 'advisor.convert_leads', __('Convert qualified leads into invoices with the InvoiceMaker.'), url('app/leads'));
        $suggest('plan-hive', 'focus-matrix', 'advisor.project_tasks', __('Break projects into focused tasks so work gets done.'), url('app/planhive'));
        $suggest('audit', 'kpi-tool', 'advisor.audit_kpis', __('Turn audit gaps into measurable KPIs in KpiTool.'), url('app/kpitool'));
        $suggest('smart-kpi', 'financial-platform', 'advisor.kpi_finance', __('Track company KPIs against financial data in FinancialPlatform.'), url('app/finance'));
        $suggest('time-butler', 'plan-hive', 'advisor.capacity_plan', __('Plan projects around team absences and availability.'), url('app/timebutler'));
        $suggest('keyword-cluster', 'lead-quality', 'advisor.cluster_leads', __('Create lead lists from keyword clusters for outreach.'), url('app/clusters'));
        $suggest('dental-track', 'cash-core', 'advisor.lab_cash', __('Record payments for dental lab orders to track cash impact.'), url('app/dentaltrack'));
        $suggest('loop-engine', 'plan-hive', 'advisor.sop_projects', __('Attach LoopEngine SOPs to PlanHive projects.'), url('app/loopengine'));
        $suggest('nur-du', 'vision-flow', 'advisor.vision_mission', __('Align your Nur-Du vision with VisionFlow missions.'), url('app/nurdu'));
        $suggest('bunny-band', 'lead-quality', 'advisor.reward_referrals', __('Reward lead referrals through BunnyBand.'), url('app/bunnyband'));
        $suggest('org-matrix', 'plan-hive', 'advisor.org_projects', __('Assign projects to organizational units.'), url('app/orgmatrix'));

        return $recommendations;
    }

    protected function tip(string $type, string $title, string $description, string $actionUrl, string $moduleKey, int $priority = 1, ?string $moduleName = null): array
    {
        return compact('type', 'title', 'description', 'actionUrl', 'moduleKey', 'priority', 'moduleName');
    }
}
