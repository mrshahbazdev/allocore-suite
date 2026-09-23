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

        // Attach to all-tools bundle
        Plan::where('slug', 'all-tools')
            ->each(fn (Plan $plan) => $plan->modules()->syncWithoutDetaching([$module->id]));

        // Create Solo Plan for RevenuePlanner
        $soloPlan = Plan::updateOrCreate(
            ['slug' => 'revenue-planner-solo'],
            [
                'name' => 'Umsatzplaner Solo',
                'description' => 'Eigenständiger Umsatz- und Zielplaner mit Lückenanalyse und Funnel-Rechner.',
                'price_monthly' => 19.99,
                'price_yearly' => 199,
                'currency' => 'EUR',
                'billable_scope' => 'both',
                'is_active' => true,
            ]
        );
        $soloPlan->modules()->syncWithoutDetaching([$module->id]);
    }

    public function down(): void
    {
        Plan::where('slug', 'revenue-planner-solo')->delete();
        Module::where('key', 'revenue-planner')->delete();
    }
};
