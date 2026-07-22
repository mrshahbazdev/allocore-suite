<?php

namespace App\Support;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\AuditPro\Models\Audit;
use Modules\BunnyBand\Models\Transaction;
use Modules\CashCore\Models\CashTransaction;
use Modules\ClusterForge\Models\KeywordCluster;
use Modules\DentalTrack\Models\Order;
use Modules\FocusMatrix\Models\Task;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\KpiTool\Models\KpiDefinition;
use Modules\LeadQuality\Models\Contact;
use Modules\LoopEngine\Models\Process;
use Modules\NurDu\Models\Vision;
use Modules\OrgMatrix\Models\Organization;
use Modules\PlanHive\Models\Project;
use Modules\SmartKpi\Models\Company;
use Modules\TimeButler\Models\AbsenceRequest;

class ModuleStats
{
    /**
     * Primary resource model for each active module.
     */
    protected array $resourceMap = [
        'invoice-maker' => ['model' => Invoice::class, 'label' => 'Invoices'],
        'audit' => ['model' => Audit::class, 'label' => 'Audits'],
        'keyword-cluster' => ['model' => KeywordCluster::class, 'label' => 'Keyword clusters'],
        'lead-quality' => ['model' => Contact::class, 'label' => 'Contacts'],
        'time-butler' => ['model' => AbsenceRequest::class, 'label' => 'Absence requests'],
        'plan-hive' => ['model' => Project::class, 'label' => 'Projects'],
        'kpi-tool' => ['model' => KpiDefinition::class, 'label' => 'KPI definitions'],
        'loop-engine' => ['model' => Process::class, 'label' => 'Processes'],
        'smart-kpi' => ['model' => Company::class, 'label' => 'Companies'],
        'cash-core' => ['model' => CashTransaction::class, 'label' => 'Transactions'],
        'bunny-band' => ['model' => Transaction::class, 'label' => 'Transactions'],
        'dental-track' => ['model' => Order::class, 'label' => 'Orders'],
        'focus-matrix' => ['model' => Task::class, 'label' => 'Tasks'],
        'org-matrix' => ['model' => Organization::class, 'label' => 'Organizations'],
        'vision-flow' => ['model' => \Modules\VisionFlow\Models\Organization::class, 'label' => 'Organizations'],
        'nur-du' => ['model' => Vision::class, 'label' => 'Vision statements'],
        'financial-platform' => ['model' => \Modules\FinancialPlatform\Models\Company::class, 'label' => 'Companies'],
    ];

    public function forUser(User $user): array
    {
        $teamId = $user->current_team_id;

        return Module::where('is_active', true)
            ->get()
            ->mapWithKeys(function (Module $module) use ($user, $teamId) {
                $mapping = $this->resourceMap[$module->key] ?? null;

                return [$module->key => [
                    'name' => $module->name,
                    'accessible' => $user->hasModule($module->key),
                    'count' => $mapping ? $this->countForModel($mapping['model'], $teamId) : null,
                    'label' => $mapping['label'] ?? null,
                ]];
            })
            ->all();
    }

    public function forModule(User $user, Module $module): array
    {
        $mapping = $this->resourceMap[$module->key] ?? null;

        return [
            'key' => $module->key,
            'name' => $module->name,
            'accessible' => $user->hasModule($module->key),
            'primary_resource' => $mapping['label'] ?? null,
            'primary_resource_count' => $mapping ? $this->countForModel($mapping['model'], $user->current_team_id) : null,
        ];
    }

    public function modelFor(string $key): ?string
    {
        return $this->resourceMap[$key]['model'] ?? null;
    }

    public function labelFor(string $key): ?string
    {
        return $this->resourceMap[$key]['label'] ?? null;
    }

    protected function countForModel(string $modelClass, ?int $teamId): ?int
    {
        if (! class_exists($modelClass)) {
            return null;
        }

        $model = new $modelClass;
        $table = $model->getTable();

        if (! Schema::hasTable($table)) {
            return null;
        }

        $query = DB::table($table);

        if (Schema::hasColumn($table, 'team_id') && $teamId) {
            $query->where('team_id', $teamId);
        }

        return $query->count();
    }
}
