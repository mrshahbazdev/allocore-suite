<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('smartkpi_companies')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('smartkpi_departments')->nullOnDelete();
            $table->foreignId('kpi_definition_id')->nullable()->constrained('smartkpi_kpi_definitions')->nullOnDelete();
            $table->string('name_en');
            $table->string('name_de')->nullable();
            $table->decimal('target_value', 18, 4)->nullable();
            $table->decimal('current_value', 18, 4)->nullable();
            $table->decimal('progress', 5, 2)->nullable();
            $table->date('deadline')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_goals');
    }
};
