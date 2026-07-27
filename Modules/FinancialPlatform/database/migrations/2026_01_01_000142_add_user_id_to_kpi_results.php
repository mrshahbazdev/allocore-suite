<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('financial_kpi_results', 'user_id')) {
            Schema::table('financial_kpi_results', function (Blueprint $table): void {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->after('team_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('financial_kpi_results', function (Blueprint $table): void {
            $table->dropColumn('user_id');
        });
    }
};
