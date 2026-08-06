<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditintelligence_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('audit_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('priority', ['low', 'medium', 'high'])->default('high');
            $table->enum('legal_relevance', ['low', 'medium', 'high'])->default('medium');
            $table->enum('implementation_effort', ['small', 'medium', 'large'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'accepted'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditintelligence_findings');
    }
};
