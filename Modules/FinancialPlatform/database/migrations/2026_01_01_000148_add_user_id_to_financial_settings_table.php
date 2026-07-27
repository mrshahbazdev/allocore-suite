<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('financial_settings', 'user_id')) {
            Schema::table('financial_settings', function (Blueprint $table): void {
                $table->foreignId('user_id')->nullable()->after('team_id')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('financial_settings', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
