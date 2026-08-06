<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['team_id', 'sort_order']);
        });

        Schema::create('sops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('sop_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('why')->nullable();
            $table->text('who')->nullable();
            $table->text('when')->nullable();
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->text('risks')->nullable();
            $table->text('tools')->nullable();
            $table->string('status')->default('draft'); // draft, published, archived
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'status']);
            $table->index(['team_id', 'category_id']);
        });

        Schema::create('sop_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained('sops')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['sop_id', 'sort_order']);
        });

        Schema::create('sop_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained('sops')->cascadeOnDelete();
            $table->foreignId('step_id')->nullable()->constrained('sop_steps')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('text');
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->index(['sop_id', 'sort_order']);
        });

        Schema::create('sop_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained('sops')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('question');
            $table->string('answer_type')->default('single'); // single, multiple, text
            $table->json('options')->nullable();
            $table->text('correct_answer')->nullable();
            $table->text('explanation')->nullable();
            $table->timestamps();
            $table->index(['sop_id', 'sort_order']);
        });

        Schema::create('sop_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained('sops')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->json('answers')->nullable();
            $table->json('checked_items')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['sop_id', 'user_id']);
        });

        Schema::create('sop_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained('sops')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_path');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['sop_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_evidence');
        Schema::dropIfExists('sop_completions');
        Schema::dropIfExists('sop_quizzes');
        Schema::dropIfExists('sop_checklist_items');
        Schema::dropIfExists('sop_steps');
        Schema::dropIfExists('sops');
        Schema::dropIfExists('sop_categories');
    }
};
