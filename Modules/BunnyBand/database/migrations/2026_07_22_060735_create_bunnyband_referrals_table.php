<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bunnyband_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referrer_id')->constrained('bunnyband_profiles')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('bunnyband_profiles')->cascadeOnDelete();
            $table->decimal('reward_amount', 8, 2)->default(5);
            $table->boolean('is_rewarded')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bunnyband_referrals');
    }
};
