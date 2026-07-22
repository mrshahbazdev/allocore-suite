<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(['key' => 'vision-flow'], [
            'name' => 'VisionFlow',
            'description' => 'Values-to-mission operating system: co-create values, principles, strategic goals, vision and missions.',
            'icon' => 'rocket-launch',
            'route_prefix' => 'visionflow',
        ]);
    }

    public function down(): void
    {
        Module::where('key', 'vision-flow')->delete();
    }
};
