<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bunnyband_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_image')->nullable();
            $table->enum('type', ['free', 'paid'])->default('free');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('daily_earning_limit', 12, 2)->default(0);
            $table->decimal('referral_bonus', 12, 2)->default(0);
            $table->decimal('task_bonus_percent', 5, 2)->default(0);
            $table->decimal('withdrawal_limit', 12, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bunnyband_levels');
    }
};
