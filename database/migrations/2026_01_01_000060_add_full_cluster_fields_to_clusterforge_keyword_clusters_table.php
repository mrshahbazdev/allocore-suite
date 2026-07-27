<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clusterforge_keyword_clusters', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->json('tags')->nullable()->after('description');
            $table->string('algorithm')->default('terms')->after('clusters');
            $table->string('status')->default('completed')->after('algorithm');
            $table->text('processing_error')->nullable()->after('status');
            $table->index(['team_id', 'status']);
            $table->index(['is_public', 'public_slug']);
        });
    }

    public function down(): void
    {
        Schema::table('clusterforge_keyword_clusters', function (Blueprint $table) {
            $table->dropColumn(['description', 'tags', 'algorithm', 'status', 'processing_error']);
        });
    }
};
