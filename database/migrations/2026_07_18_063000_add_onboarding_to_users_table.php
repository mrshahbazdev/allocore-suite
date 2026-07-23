<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('onboarding_step')->default(0)->after('theme');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_step');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['onboarding_step', 'onboarding_completed_at']);
        });
    }
};
