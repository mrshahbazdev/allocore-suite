<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('focusmatrix_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('inbox');
            $table->string('only_you_category', 32)->nullable();
            $table->string('source', 32)->default('manual');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('focused_block_at')->nullable();
            $table->string('ai_suggestion', 255)->nullable();
            $table->float('ai_confidence')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'user_id', 'status']);
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('focusmatrix_delegations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('delegator_id');
            $table->unsignedBigInteger('delegate_user_id')->nullable();
            $table->unsignedBigInteger('original_owner_id')->nullable();
            $table->string('delegate_name_fallback')->nullable();
            $table->text('goal');
            $table->string('decision_scope', 32)->default('consult');
            $table->timestamp('deadline')->nullable();
            $table->text('resources')->nullable();
            $table->json('inform_list')->nullable();
            $table->boolean('no_micromanagement')->default(true);
            $table->string('status', 32)->default('open');
            $table->integer('health_score')->default(100);
            $table->timestamp('last_checkin_at')->nullable();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->string('invite_token', 64)->nullable();
            $table->timestamps();
            $table->index(['team_id', 'delegator_id', 'status']);
            $table->unique('task_id');
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('task_id')->references('id')->on('focusmatrix_tasks')->cascadeOnDelete();
            $table->foreign('delegator_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('delegate_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('original_owner_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('focusmatrix_kill_list_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('item_type', 32)->default('task');
            $table->string('title');
            $table->text('reason')->nullable();
            $table->boolean('was_necessary')->nullable();
            $table->boolean('served_clear_goal')->nullable();
            $table->boolean('anything_missing')->nullable();
            $table->timestamp('killed_at')->useCurrent();
            $table->timestamps();
            $table->index(['team_id', 'user_id']);
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('focusmatrix_tasks')->nullOnDelete();
        });

        Schema::create('focusmatrix_self_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('week');
            $table->text('q1_others_could_do')->nullable();
            $table->text('q2_delegated_late')->nullable();
            $table->text('q3_to_omit_next_week')->nullable();
            $table->text('q4_focused_decisions')->nullable();
            $table->integer('focus_score')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'year', 'week']);
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('focusmatrix_org_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('week');
            $table->boolean('decides_what_clear')->nullable();
            $table->boolean('responsibilities_clear')->nullable();
            $table->boolean('reports_short')->nullable();
            $table->boolean('teams_small')->nullable();
            $table->text('notes')->nullable();
            $table->integer('health_score')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'year', 'week']);
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('focusmatrix_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('color', 16)->default('accent');
            $table->boolean('all_day')->default(false);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('source', 32)->default('manual');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'user_id', 'starts_at']);
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('focusmatrix_tasks')->nullOnDelete();
        });

        Schema::create('focusmatrix_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider');
            $table->string('account_email')->nullable();
            $table->string('label')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'provider']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('focusmatrix_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('provider')->default('gemini');
            $table->text('api_key_encrypted')->nullable();
            $table->string('model')->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('calls_this_month')->default(0);
            $table->unsignedInteger('monthly_limit')->default(200);
            $table->timestamp('last_called_at')->nullable();
            $table->timestamp('quota_reset_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('focusmatrix_user_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('ics_token', 64)->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('focusmatrix_user_settings');
        Schema::dropIfExists('focusmatrix_ai_settings');
        Schema::dropIfExists('focusmatrix_integrations');
        Schema::dropIfExists('focusmatrix_calendar_events');
        Schema::dropIfExists('focusmatrix_org_checks');
        Schema::dropIfExists('focusmatrix_self_checks');
        Schema::dropIfExists('focusmatrix_kill_list_items');
        Schema::dropIfExists('focusmatrix_delegations');
        Schema::dropIfExists('focusmatrix_tasks');
    }
};
