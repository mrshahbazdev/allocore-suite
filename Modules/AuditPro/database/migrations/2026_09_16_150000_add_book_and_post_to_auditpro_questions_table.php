<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditpro_questions', function (Blueprint $table) {
            if (! Schema::hasColumn('auditpro_questions', 'recommended_book_id')) {
                $table->unsignedBigInteger('recommended_book_id')->nullable()->after('knowledge_slug');
            }
            if (! Schema::hasColumn('auditpro_questions', 'recommended_post_id')) {
                $table->unsignedBigInteger('recommended_post_id')->nullable()->after('recommended_book_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('auditpro_questions', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('auditpro_questions', 'recommended_book_id')) {
                $drop[] = 'recommended_book_id';
            }
            if (Schema::hasColumn('auditpro_questions', 'recommended_post_id')) {
                $drop[] = 'recommended_post_id';
            }
            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
