<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(
            ['key' => 'kpi-tool'],
            [
                'name' => 'KpiTool',
                'description' => 'Bilingual KPI catalog, monthly spreadsheet, targets, charts & CSV export.',
                'icon' => 'chart-bar',
                'route_prefix' => 'kpitool',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        Module::where('key', 'kpi-tool')->delete();
    }
};
