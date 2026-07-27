<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditpro_audits', function (Blueprint $table) {
            $table->string('focus_pillar')->nullable()->after('audit_type');

            $table->index(['team_id', 'audit_type', 'focus_pillar', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('auditpro_audits', function (Blueprint $table) {
            $table->dropColumn('focus_pillar');
        });
    }
};
