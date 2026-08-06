<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devmanager_user_stories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('idea_id')->constrained('devmanager_ideas')->cascadeOnDelete();
            $table->foreignId('requirement_id')->nullable()->constrained('devmanager_requirements')->nullOnDelete();
            $table->string('role');
            $table->string('action');
            $table->string('benefit')->nullable();
            $table->text('acceptance_criteria')->nullable();
            $table->unsignedInteger('story_points')->nullable();
            $table->string('status')->default('todo');
            $table->unsignedInteger('position')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['idea_id', 'status']);
            $table->index(['requirement_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devmanager_user_stories');
    }
};
