<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bunnyband_user_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bunnyband_profile_id')->constrained('bunnyband_profiles')->cascadeOnDelete();
            $table->foreignId('bunnyband_task_id')->constrained('bunnyband_tasks')->cascadeOnDelete();
            $table->enum('status', ['pending', 'completed', 'verified', 'rejected'])->default('pending');
            $table->string('proof')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bunnyband_user_tasks');
    }
};
