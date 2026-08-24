<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditpro_questions', function (Blueprint $table) {
            $table->string('recommended_module_key')->nullable()->after('failure_recommendation');
            $table->string('knowledge_slug')->nullable()->after('recommended_module_key');
        });
    }

    public function down(): void
    {
        Schema::table('auditpro_questions', function (Blueprint $table) {
            $table->dropColumn(['recommended_module_key', 'knowledge_slug']);
        });
    }
};
