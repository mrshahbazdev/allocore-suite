<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(
            ['key' => 'plan-hive'],
            [
                'name' => 'PlanHive',
                'description' => 'Multi-tenant project management with tasks, goals, calendar, contacts & documents.',
                'icon' => 'folder',
                'route_prefix' => 'planhive',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        Module::where('key', 'plan-hive')->delete();
    }
};
