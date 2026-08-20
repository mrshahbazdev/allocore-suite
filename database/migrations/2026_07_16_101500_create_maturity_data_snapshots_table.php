<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maturity_data_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id');
            $table->foreignId('audit_id')->nullable();
            $table->foreignId('allocore_score_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('industry')->nullable();
            $table->string('industry_sub')->nullable();
            $table->string('size')->nullable();
            $table->unsignedTinyInteger('company_age')->nullable();
            $table->string('country')->nullable();
            $table->string('revenue_range')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->string('maturity_level')->nullable();
            $table->json('pillars')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique('team_id');
            $table->index('industry');
            $table->index(['size', 'score']);
            $table->index(['country', 'score']);
            $table->index(['revenue_range', 'score']);
            $table->index('calculated_at');

            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('audit_id')->references('id')->on('auditpro_audits')->nullOnDelete();
            $table->foreign('allocore_score_id')->references('id')->on('allocore_scores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maturity_data_snapshots');
    }
};
