<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('industry_sub')->nullable()->after('industry');
        });

        Schema::table('auditpro_audits', function (Blueprint $table) {
            $table->string('industry_sub')->nullable()->after('industry');
        });

        Schema::table('allocore_scores', function (Blueprint $table) {
            $table->string('industry_sub')->nullable()->after('industry');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('industry_sub');
        });

        Schema::table('auditpro_audits', function (Blueprint $table) {
            $table->dropColumn('industry_sub');
        });

        Schema::table('allocore_scores', function (Blueprint $table) {
            $table->dropColumn('industry_sub');
        });
    }
};
