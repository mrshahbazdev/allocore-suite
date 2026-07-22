<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(
            ['key' => 'loop-engine'],
            [
                'name' => 'LoopEngine',
                'description' => 'Decision loop SOP builder with step-by-step execution, self-checking loops, audit trails and webhooks.',
                'icon' => 'arrow-path',
                'route_prefix' => 'loopengine',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        Module::where('key', 'loop-engine')->delete();
    }
};
