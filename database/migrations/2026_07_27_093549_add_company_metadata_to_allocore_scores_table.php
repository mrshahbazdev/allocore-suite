<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocore_scores', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('audit_id');
            $table->string('industry')->nullable()->after('company_name');
            $table->string('size')->nullable()->after('industry');
            $table->unsignedTinyInteger('company_age')->nullable()->after('size');

            $table->index(['industry', 'score']);
            $table->index(['size', 'score']);
        });
    }

    public function down(): void
    {
        Schema::table('allocore_scores', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'industry', 'size', 'company_age']);
        });
    }
};
