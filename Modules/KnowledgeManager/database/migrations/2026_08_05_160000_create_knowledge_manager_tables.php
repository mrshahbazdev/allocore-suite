<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->string('url')->nullable();
            $table->string('industry')->nullable();
            $table->string('stage')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('knowledge_projects')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('section');
            $table->string('question_key');
            $table->text('answer')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'section', 'question_key']);
        });

        Schema::create('knowledge_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('knowledge_projects')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('link')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_assets');
        Schema::dropIfExists('knowledge_answers');
        Schema::dropIfExists('knowledge_projects');
    }
};
