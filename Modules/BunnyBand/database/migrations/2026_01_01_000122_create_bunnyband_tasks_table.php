<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bunnyband_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['social_follow', 'app_install', 'website_visit', 'video_watch', 'game_play', 'daily_checkin']);
            $table->decimal('reward', 8, 2)->default(2);
            $table->string('url')->nullable();
            $table->enum('verification_method', ['manual', 'automatic', 'timer'])->default('manual');
            $table->boolean('is_active')->default(true);
            $table->integer('max_completions')->nullable();
            $table->integer('cooldown_hours')->default(24);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bunnyband_tasks');
    }
};
