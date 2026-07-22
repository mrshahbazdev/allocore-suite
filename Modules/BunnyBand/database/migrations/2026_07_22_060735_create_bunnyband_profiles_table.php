<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bunnyband_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('bunnyband_levels')->nullOnDelete();
            $table->string('referral_code')->unique();
            $table->foreignId('referred_by')->nullable()->constrained('bunnyband_profiles')->nullOnDelete();
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('task_earnings', 12, 2)->default(0);
            $table->decimal('referral_earnings', 12, 2)->default(0);
            $table->integer('total_referrals')->default(0);
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('level_upgraded_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
            $table->index(['team_id', 'referral_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bunnyband_profiles');
    }
};
