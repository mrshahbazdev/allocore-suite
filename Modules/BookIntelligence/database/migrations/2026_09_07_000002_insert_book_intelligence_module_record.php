<?php

use App\Models\Module;
use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $module = Module::updateOrCreate(
            ['key' => 'book-intelligence'],
            [
                'name' => 'Knowledge Library',
                'description' => 'Turn books into an organized, role-relevant learning library with clear reading progress.',
                'icon' => 'book-open',
                'route_prefix' => 'books',
                'is_active' => true,
            ],
        );

        Plan::where('slug', 'all-tools')
            ->each(fn (Plan $plan) => $plan->modules()->syncWithoutDetaching([$module->id]));
    }

    public function down(): void
    {
        Module::where('key', 'book-intelligence')->delete();
    }
};
