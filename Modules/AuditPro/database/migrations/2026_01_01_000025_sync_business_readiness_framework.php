<?php

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditTemplate;
use Modules\AuditPro\Services\DefaultTemplateProvisioner;

return new class extends Migration
{
    public function up(): void
    {
        $teamIds = AuditTemplate::withoutGlobalScopes()
            ->where('slug', 'business-maturity')
            ->pluck('team_id')
            ->unique();
        $provisioner = app(DefaultTemplateProvisioner::class);

        foreach ($teamIds as $teamId) {
            $team = Team::find($teamId);

            if ($team) {
                $provisioner->synchronize($team);
            }
        }

        $orderPillarIds = AuditPillar::withoutGlobalScopes()
            ->where('name', 'Order')
            ->pluck('id');

        DB::table('auditpro_results')
            ->whereIn('pillar_id', $orderPillarIds)
            ->update(['level' => 'Order']);
    }

    public function down(): void {}
};
