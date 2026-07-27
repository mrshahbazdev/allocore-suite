<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditpro_audits', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('template_id');
            $table->string('industry')->nullable()->after('company_name');
            $table->string('size')->nullable()->after('industry');
            $table->unsignedTinyInteger('company_age')->nullable()->after('size');
            $table->string('audit_type')->default('major')->after('status');
            $table->timestamp('completed_at')->nullable()->after('updated_at');

            $table->index(['team_id', 'audit_type', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('auditpro_audits', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'industry', 'size', 'company_age', 'audit_type', 'completed_at']);
        });
    }
};
