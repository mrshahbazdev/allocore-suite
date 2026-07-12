<?php

namespace Modules\AuditPro\Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Modules\AuditPro\Services\DefaultTemplateProvisioner;

class AuditProDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provisioner = app(DefaultTemplateProvisioner::class);

        Team::query()->each(fn (Team $team) => $provisioner->provision($team));
    }
}
