<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(['key' => 'nur-du'], [
            'name' => 'Nur-Du',
            'description' => 'Vision alignment tool: vision, guiding principles, quarterly priorities, decisions and vision checks.',
            'icon' => 'star',
            'route_prefix' => 'nurdu',
        ]);
    }

    public function down(): void
    {
        Module::where('key', 'nur-du')->delete();
    }
};
