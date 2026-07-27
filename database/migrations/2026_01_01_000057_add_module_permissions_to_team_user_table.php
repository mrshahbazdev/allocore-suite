<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->json('allowed_modules')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->dropColumn('allowed_modules');
        });
    }
};
