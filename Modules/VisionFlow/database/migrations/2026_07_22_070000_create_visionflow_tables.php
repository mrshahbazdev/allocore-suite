<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visionflow_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'slug']);
        });

        Schema::create('visionflow_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('visionflow_organizations')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('visionflow_principles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('visionflow_organizations')->cascadeOnDelete();
            $table->foreignId('value_id')->constrained('visionflow_values')->cascadeOnDelete();
            $table->text('statement');
            $table->string('status', 32)->default('draft');
            $table->decimal('alignment_score', 5, 2)->default(0);
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('visionflow_strategic_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('visionflow_organizations')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 32);
            $table->string('time_horizon')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();
            $table->index(['organization_id', 'category']);
        });

        Schema::create('visionflow_goal_value', function (Blueprint $table) {
            $table->foreignId('strategic_goal_id')->constrained('visionflow_strategic_goals')->cascadeOnDelete();
            $table->foreignId('value_id')->constrained('visionflow_values')->cascadeOnDelete();
            $table->primary(['strategic_goal_id', 'value_id']);
        });

        Schema::create('visionflow_goal_principle', function (Blueprint $table) {
            $table->foreignId('strategic_goal_id')->constrained('visionflow_strategic_goals')->cascadeOnDelete();
            $table->foreignId('principle_id')->constrained('visionflow_principles')->cascadeOnDelete();
            $table->primary(['strategic_goal_id', 'principle_id']);
        });

        Schema::create('visionflow_visions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('visionflow_organizations')->cascadeOnDelete();
            $table->text('content');
            $table->string('status', 32)->default('drafting');
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            $table->index(['organization_id', 'is_current']);
        });

        Schema::create('visionflow_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('visionflow_organizations')->cascadeOnDelete();
            $table->foreignId('vision_id')->constrained('visionflow_visions')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('active');
            $table->string('review_cadence')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('visionflow_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('visionflow_organizations')->cascadeOnDelete();
            $table->foreignId('mission_id')->constrained('visionflow_missions')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('visionflow_decision_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('visionflow_organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('decision');
            $table->foreignId('supporting_value_id')->nullable()->constrained('visionflow_values')->nullOnDelete();
            $table->foreignId('supporting_mission_id')->nullable()->constrained('visionflow_missions')->nullOnDelete();
            $table->timestamps();
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visionflow_decision_logs');
        Schema::dropIfExists('visionflow_projects');
        Schema::dropIfExists('visionflow_missions');
        Schema::dropIfExists('visionflow_visions');
        Schema::dropIfExists('visionflow_goal_principle');
        Schema::dropIfExists('visionflow_goal_value');
        Schema::dropIfExists('visionflow_strategic_goals');
        Schema::dropIfExists('visionflow_principles');
        Schema::dropIfExists('visionflow_values');
        Schema::dropIfExists('visionflow_organizations');
    }
};
