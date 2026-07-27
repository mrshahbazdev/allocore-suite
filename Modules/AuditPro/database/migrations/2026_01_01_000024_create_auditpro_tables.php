<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('industry')->nullable()->after('name');
            $table->string('size')->nullable()->after('industry');
        });

        Schema::create('auditpro_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['team_id', 'slug']);
        });

        Schema::create('auditpro_pillars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('auditpro_templates')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('target_score', 3, 1)->default(5);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('auditpro_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('auditpro_templates')->cascadeOnDelete();
            $table->foreignId('pillar_id')->constrained('auditpro_pillars')->cascadeOnDelete();
            $table->text('question');
            $table->text('description')->nullable();
            $table->string('question_type')->default('scale_1_to_5');
            $table->decimal('weight', 4, 2)->default(1);
            $table->boolean('is_required')->default(true);
            $table->text('failure_recommendation')->nullable();
            $table->json('options')->nullable();
            $table->foreignId('depends_on_question_id')->nullable()->constrained('auditpro_questions')->nullOnDelete();
            $table->string('depends_on_answer')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('auditpro_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('auditpro_templates')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('in_progress');
            $table->timestamps();
            $table->index(['team_id', 'status']);
        });

        Schema::create('auditpro_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audit_id')->constrained('auditpro_audits')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('auditpro_questions')->cascadeOnDelete();
            $table->json('value')->nullable();
            $table->text('comment')->nullable();
            $table->string('evidence_file_path')->nullable();
            $table->timestamps();
            $table->unique(['audit_id', 'question_id']);
        });

        Schema::create('auditpro_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audit_id')->constrained('auditpro_audits')->cascadeOnDelete();
            $table->foreignId('pillar_id')->nullable()->constrained('auditpro_pillars')->nullOnDelete();
            $table->string('level');
            $table->decimal('average_score', 8, 2)->default(0);
            $table->string('maturity_level')->nullable();
            $table->decimal('total_points', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['audit_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditpro_results');
        Schema::dropIfExists('auditpro_answers');
        Schema::dropIfExists('auditpro_audits');
        Schema::dropIfExists('auditpro_questions');
        Schema::dropIfExists('auditpro_pillars');
        Schema::dropIfExists('auditpro_templates');

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['industry', 'size']);
        });
    }
};
