<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $completed = DB::table('site_settings')
            ->where('key', 'like', 'setup_wizard_completed_%')
            ->whereIn('value', ['1', 'true'])
            ->exists();

        if ($completed) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => 'setup_wizard_completed'],
                ['value' => '1', 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('site_settings')->where('key', 'setup_wizard_completed')->delete();
    }
};
