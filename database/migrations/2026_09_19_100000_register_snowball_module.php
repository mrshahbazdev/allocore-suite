<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('modules')->where('key', 'snowball')->exists();
        if (! $exists) {
            DB::table('modules')->insert([
                'key' => 'snowball',
                'name' => 'Debt Snowball Tracker',
                'description' => 'Strategischer Schuldenabbau, Tilgungsbeschleuniger & Snowball vs. Avalanche Amortisation für den Mittelstand.',
                'category' => 'Profit',
                'icon' => 'heroicon-o-banknotes',
                'route_prefix' => 'snowball',
                'is_active' => true,
                'in_subscription_pool' => true,
                'is_deprecated' => false,
                'badge_text' => 'Finance',
                'sort_order' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('modules')->where('key', 'snowball')->delete();
    }
};
