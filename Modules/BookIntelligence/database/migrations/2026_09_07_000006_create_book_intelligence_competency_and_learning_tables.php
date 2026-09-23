<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('bookintelligence_user_expertise_profiles');
        Schema::dropIfExists('bookintelligence_challenge_submissions');
        Schema::dropIfExists('bookintelligence_practical_challenges');
        Schema::dropIfExists('bookintelligence_assessment_submissions');
        Schema::dropIfExists('bookintelligence_skill_assessments');
        Schema::dropIfExists('bookintelligence_learning_paths');
        Schema::dropIfExists('bookintelligence_career_paths');
        Schema::dropIfExists('bookintelligence_competency_roles');

        // Module 10: Role-Based Competency Framework
        Schema::create('bookintelligence_competency_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('department', 64)->default('General'); // Sales, Operations, CS, Leadership, RevOps, Product
            $table->string('level', 32)->default('professional'); // starter, junior, professional, senior, expert, master
            $table->text('description')->nullable();
            $table->json('required_knowledge')->nullable();
            $table->json('required_competencies')->nullable();
            $table->json('required_skills')->nullable();
            $table->unsignedBigInteger('next_role_id')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'slug'], 'bi_comp_roles_slug_uniq');
            $table->index(['team_id', 'department'], 'bi_comp_roles_dept_idx');
        });

        // Module 11: Career Progression Engine
        Schema::create('bookintelligence_career_paths', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_role_id')->constrained('bookintelligence_competency_roles')->cascadeOnDelete();
            $table->foreignId('to_role_id')->constrained('bookintelligence_competency_roles')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('required_competencies')->nullable();
            $table->json('recommended_book_ids')->nullable();
            $table->json('milestones')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'from_role_id'], 'bi_cpath_from_idx');
            $table->index(['team_id', 'to_role_id'], 'bi_cpath_to_idx');
        });

        // Module 12: Personalized Learning Paths
        Schema::create('bookintelligence_learning_paths', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_role_id')->nullable()->constrained('bookintelligence_competency_roles')->nullOnDelete();
            $table->string('title');
            $table->string('status', 24)->default('active'); // active, completed, paused
            $table->json('steps')->nullable(); // array of learning sequence steps
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->text('ai_rationale')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'user_id'], 'bi_lpaths_user_idx');
            $table->index(['team_id', 'status'], 'bi_lpaths_status_idx');
        });

        // Module 13: Skill Assessment Engine
        Schema::create('bookintelligence_skill_assessments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained('bookintelligence_books')->nullOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('bookintelligence_competency_roles')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('questions')->nullable(); // array of quiz questions with options and explanations
            $table->unsignedSmallInteger('passing_score')->default(70);
            $table->unsignedSmallInteger('time_limit_minutes')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'book_id'], 'bi_assess_book_idx');
            $table->index(['team_id', 'role_id'], 'bi_assess_role_idx');
        });

        Schema::create('bookintelligence_assessment_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained('bookintelligence_skill_assessments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('answers')->nullable();
            $table->decimal('score', 5, 2)->default(0.00);
            $table->boolean('passed')->default(false);
            $table->json('strengths')->nullable();
            $table->json('gaps')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'user_id'], 'bi_asub_user_idx');
            $table->index(['team_id', 'assessment_id'], 'bi_asub_assess_idx');
        });

        // Module 14: Practical Learning & Challenge Engine
        Schema::create('bookintelligence_practical_challenges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained('bookintelligence_books')->nullOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('bookintelligence_competency_roles')->nullOnDelete();
            $table->string('title');
            $table->text('scenario_description');
            $table->text('assignment_brief');
            $table->string('deliverable_format')->default('Written Strategy Document');
            $table->json('reflection_questions')->nullable();
            $table->json('evaluation_rubric')->nullable();
            $table->string('difficulty', 24)->default('intermediate');
            $table->timestamps();

            $table->index(['team_id', 'book_id'], 'bi_chall_book_idx');
            $table->index(['team_id', 'role_id'], 'bi_chall_role_idx');
        });

        Schema::create('bookintelligence_challenge_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained('bookintelligence_practical_challenges')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->longText('submission_text');
            $table->string('submission_attachment_url')->nullable();
            $table->json('reflection_answers')->nullable();
            $table->string('status', 24)->default('submitted'); // submitted, graded, needs_revision
            $table->decimal('grade_score', 5, 2)->nullable();
            $table->text('evaluator_feedback')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'user_id'], 'bi_csub_user_idx');
            $table->index(['team_id', 'challenge_id'], 'bi_csub_chall_idx');
        });

        // Module 15: Expertise Progression System
        Schema::create('bookintelligence_user_expertise_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_role_id')->nullable()->constrained('bookintelligence_competency_roles')->nullOnDelete();
            $table->foreignId('target_role_id')->nullable()->constrained('bookintelligence_competency_roles')->nullOnDelete();
            $table->string('expertise_level', 32)->default('starter'); // starter, junior, professional, senior, expert, master
            $table->unsignedInteger('points')->default(0);
            $table->unsignedSmallInteger('books_read_count')->default(0);
            $table->unsignedSmallInteger('assessments_passed_count')->default(0);
            $table->unsignedSmallInteger('challenges_completed_count')->default(0);
            $table->json('badges')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'user_id'], 'bi_user_exp_uniq');
            $table->index(['team_id', 'expertise_level'], 'bi_user_exp_lvl_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookintelligence_user_expertise_profiles');
        Schema::dropIfExists('bookintelligence_challenge_submissions');
        Schema::dropIfExists('bookintelligence_practical_challenges');
        Schema::dropIfExists('bookintelligence_assessment_submissions');
        Schema::dropIfExists('bookintelligence_skill_assessments');
        Schema::dropIfExists('bookintelligence_learning_paths');
        Schema::dropIfExists('bookintelligence_career_paths');
        Schema::dropIfExists('bookintelligence_competency_roles');
    }
};
