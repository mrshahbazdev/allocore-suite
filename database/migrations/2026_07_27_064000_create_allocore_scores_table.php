<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocore_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audit_id')->constrained('auditpro_audits')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0);
            $table->string('maturity_level')->nullable();
            $table->json('pillars')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['team_id', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocore_scores');
    }
};
