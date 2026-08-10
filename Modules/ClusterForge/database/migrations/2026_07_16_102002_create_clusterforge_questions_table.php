<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clusterforge_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subtopic_id')->constrained('clusterforge_subtopics')->cascadeOnDelete();
            $table->text('question');
            $table->longText('answer')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clusterforge_questions');
    }
};
