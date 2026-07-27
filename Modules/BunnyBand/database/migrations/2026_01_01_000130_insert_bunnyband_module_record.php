<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(
            ['key' => 'bunny-band'],
            [
                'name' => 'BunnyBand',
                'description' => 'Reward-based micro-task platform with tasks, referrals, levels, wallet, deposits and withdrawals.',
                'icon' => 'gift',
                'route_prefix' => 'bunnyband',
            ]
        );
    }

    public function down(): void
    {
        Module::where('key', 'bunny-band')->delete();
    }
};
