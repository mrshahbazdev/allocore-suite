<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(
            ['key' => 'time-butler'],
            [
                'name' => 'TimeButler',
                'description' => 'Employee vacation, absence & time tracking with team calendar and German holidays.',
                'icon' => 'clock',
                'route_prefix' => 'timebutler',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        Module::where('key', 'time-butler')->delete();
    }
};
