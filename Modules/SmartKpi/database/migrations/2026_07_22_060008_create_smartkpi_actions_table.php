<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('problem_id')->constrained('smartkpi_problems')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium');
            $table->date('due_date')->nullable();
            $table->string('status')->default('open');
            $table->unsignedTinyInteger('effectiveness_score')->nullable();
            $table->timestamps();
            $table->index(['problem_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_actions');
    }
};
