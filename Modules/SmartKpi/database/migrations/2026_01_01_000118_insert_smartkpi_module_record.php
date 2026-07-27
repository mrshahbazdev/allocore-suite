<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::firstOrCreate(
            ['key' => 'smart-kpi'],
            ['name' => 'SmartKpi', 'description' => 'Hierarchical multi-tenant KPI management with problems, actions, forecasts and goals.', 'icon' => 'presentation-chart-line', 'route_prefix' => 'smartkpi', 'is_active' => true]
        );
    }

    public function down(): void
    {
        Module::where('key', 'smart-kpi')->delete();
    }
};
