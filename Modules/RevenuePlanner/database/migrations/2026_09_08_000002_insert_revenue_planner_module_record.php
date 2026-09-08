<?php

use App\Models\Module;
use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $module = Module::updateOrCreate(
            ['key' => 'revenue-planner'],
            [
                'name' => 'Umsatzplaner',
                'description' => 'Ermitteln Sie Ihren benötigten Zielumsatz, analysieren Sie Umsatzlücken und berechnen Sie den genauen Neukundenbedarf.',
                'icon' => 'calculator',
                'route_prefix' => 'revenue-planner',
                'is_active' => true,
            ],
        );

        Plan::where('slug', 'all-tools')
            ->each(fn (Plan $plan) => $plan->modules()->syncWithoutDetaching([$module->id]));
    }

    public function down(): void
    {
        Module::where('key', 'revenue-planner')->delete();
    }
};
