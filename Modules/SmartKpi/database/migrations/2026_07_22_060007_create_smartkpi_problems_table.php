<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_problems', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_definition_id')->constrained('smartkpi_kpi_definitions')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('smartkpi_companies')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('smartkpi_departments')->nullOnDelete();
            $table->foreignId('detected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity');
            $table->string('status')->default('open');
            $table->date('detected_at')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'status']);
            $table->index(['kpi_definition_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_problems');
    }
};
