<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasColumn = Schema::hasColumn('auditpro_audits', 'focus_pillar');

        Schema::table('auditpro_audits', function (Blueprint $table) use ($hasColumn) {
            if (! $hasColumn) {
                $table->string('focus_pillar')->nullable()->after('audit_type');
            }

            $table->index(['team_id', 'audit_type', 'focus_pillar', 'completed_at'], 'idx_aap_focus');
        });
    }

    public function down(): void
    {
        $hasColumn = Schema::hasColumn('auditpro_audits', 'focus_pillar');

        Schema::table('auditpro_audits', function (Blueprint $table) use ($hasColumn) {
            if ($hasColumn) {
                $table->dropColumn('focus_pillar');
            }
        });
    }
};
