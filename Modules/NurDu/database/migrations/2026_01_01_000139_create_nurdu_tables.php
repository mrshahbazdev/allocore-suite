<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nurdu_visions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('statement');
            $table->timestamps();
            $table->unique('team_id');
        });

        Schema::create('nurdu_guiding_principles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vision_id')->constrained('nurdu_visions')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('nurdu_quarterly_focuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('quarter', 2);
            $table->unsignedSmallInteger('year');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'quarter', 'year']);
        });

        Schema::create('nurdu_strategic_priorities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarterly_focus_id')->constrained('nurdu_quarterly_focuses')->cascadeOnDelete();
            $table->string('title');
            $table->string('owner')->nullable();
            $table->string('kpi')->nullable();
            $table->string('status', 16)->default('on_track');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('nurdu_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('alignment', 16);
            $table->text('justification')->nullable();
            $table->date('decision_date')->nullable();
            $table->timestamps();
        });

        Schema::create('nurdu_vision_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('check_date');
            $table->string('q1_answer', 16)->nullable();
            $table->text('q2_answer')->nullable();
            $table->text('q3_answer')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('nurdu_action_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vision_check_id')->constrained('nurdu_vision_checks')->cascadeOnDelete();
            $table->string('title');
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nurdu_action_items');
        Schema::dropIfExists('nurdu_vision_checks');
        Schema::dropIfExists('nurdu_decisions');
        Schema::dropIfExists('nurdu_strategic_priorities');
        Schema::dropIfExists('nurdu_quarterly_focuses');
        Schema::dropIfExists('nurdu_guiding_principles');
        Schema::dropIfExists('nurdu_visions');
    }
};
