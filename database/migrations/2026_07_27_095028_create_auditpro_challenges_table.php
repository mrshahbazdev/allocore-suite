<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditpro_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('small_audit_id')->constrained('auditpro_audits')->cascadeOnDelete();
            $table->string('pillar');
            $table->string('status')->default('open');
            $table->json('steps');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('next_challenge_at')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'pillar', 'status'], 'idx_apc_team_pillar_status');
            $table->index(['team_id', 'small_audit_id'], 'idx_apc_team_audit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditpro_challenges');
    }
};
