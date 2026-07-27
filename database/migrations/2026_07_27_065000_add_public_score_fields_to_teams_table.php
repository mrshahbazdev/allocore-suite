<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('public_score_enabled')->default(false)->after('ssl_last_error');
            $table->string('public_score_slug')->nullable()->unique()->after('public_score_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['public_score_enabled', 'public_score_slug']);
        });
    }
};
