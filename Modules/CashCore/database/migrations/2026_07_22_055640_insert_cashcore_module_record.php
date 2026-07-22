<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(
            ['key' => 'cash-core'],
            [
                'name' => 'CashCore',
                'description' => 'Profit First financial intelligence: cash transparency, leak detection, expense scoring, cash unlocker, behavioral reviews, scenarios and profit allocation.',
                'icon' => 'banknotes',
                'route_prefix' => 'cashcore',
            ]
        );
    }

    public function down(): void
    {
        Module::where('key', 'cash-core')->delete();
    }
};
