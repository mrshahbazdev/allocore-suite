<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $blueprintHas = Schema::hasColumn('auditpro_pillar_question_blueprints', 'parent_id');
        $questionHas = Schema::hasColumn('auditpro_questions', 'parent_id');

        if (! $blueprintHas) {
            Schema::table('auditpro_pillar_question_blueprints', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('auditpro_pillar_question_blueprints')->nullOnDelete();
            });
        }

        if (! $questionHas) {
            Schema::table('auditpro_questions', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('auditpro_questions')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $blueprintHas = Schema::hasColumn('auditpro_pillar_question_blueprints', 'parent_id');
        $questionHas = Schema::hasColumn('auditpro_questions', 'parent_id');

        if ($blueprintHas) {
            Schema::table('auditpro_pillar_question_blueprints', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            });
        }

        if ($questionHas) {
            Schema::table('auditpro_questions', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            });
        }
    }
};
