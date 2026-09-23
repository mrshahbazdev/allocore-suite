<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookintelligence_question_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('bookintelligence_books')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('question');
            $table->text('answer_excerpt')->nullable();
            $table->text('problem_statement')->nullable();
            $table->json('target_audience')->nullable();
            $table->text('when_to_read_trigger')->nullable();
            $table->string('category', 64)->nullable();
            $table->unsignedSmallInteger('priority')->default(1);
            $table->unsignedBigInteger('audit_question_id')->nullable();
            $table->string('module_key', 64)->nullable();
            $table->text('tool_explanation')->nullable();
            $table->unsignedBigInteger('glossary_term_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['team_id', 'book_id']);
            $table->index(['team_id', 'category']);
            $table->index(['team_id', 'is_active']);
        });

        Schema::create('bookintelligence_search_queries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('query');
            $table->text('ai_answer')->nullable();
            $table->foreignId('matched_book_id')->nullable()->constrained('bookintelligence_books')->nullOnDelete();
            $table->json('matched_book_ids')->nullable();
            $table->boolean('has_results')->default(true);
            $table->boolean('is_resolved')->default(true);
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'has_results']);
            $table->index(['team_id', 'is_resolved']);
        });

        Schema::create('bookintelligence_knowledge_gaps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('query');
            $table->string('status', 24)->default('open'); // open, reviewing, resolved, dismissed
            $table->unsignedInteger('search_count')->default(1);
            $table->json('suggested_books')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookintelligence_knowledge_gaps');
        Schema::dropIfExists('bookintelligence_search_queries');
        Schema::dropIfExists('bookintelligence_question_mappings');
    }
};
