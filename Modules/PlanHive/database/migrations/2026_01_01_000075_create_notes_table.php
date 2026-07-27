<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planhive_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('planhive_projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planhive_notes');
    }
};
