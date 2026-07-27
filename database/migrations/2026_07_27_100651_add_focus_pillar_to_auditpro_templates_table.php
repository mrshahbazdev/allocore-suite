<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasColumn = Schema::hasColumn('auditpro_templates', 'focus_pillar');

        Schema::table('auditpro_templates', function (Blueprint $table) use ($hasColumn) {
            if (! $hasColumn) {
                $table->string('focus_pillar')->nullable()->after('slug');
            }

            $table->index(['team_id', 'focus_pillar'], 'idx_apt_team_focus');
        });
    }

    public function down(): void
    {
        $hasColumn = Schema::hasColumn('auditpro_templates', 'focus_pillar');

        Schema::table('auditpro_templates', function (Blueprint $table) use ($hasColumn) {
            if ($hasColumn) {
                $table->dropColumn('focus_pillar');
            }
        });
    }
};
