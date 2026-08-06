<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devmanager_releases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('idea_id')->constrained('devmanager_ideas')->cascadeOnDelete();
            $table->string('version');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('changelog')->nullable();
            $table->date('released_at')->nullable();
            $table->string('status')->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['idea_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devmanager_releases');
    }
};
