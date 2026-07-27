<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planhive_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('planhive_projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('target_date')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['team_id', 'project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planhive_goals');
    }
};
