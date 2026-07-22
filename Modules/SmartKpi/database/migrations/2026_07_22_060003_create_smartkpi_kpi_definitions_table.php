<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_kpi_definitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('smartkpi_companies')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('smartkpi_departments')->nullOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name_en');
            $table->string('name_de')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_de')->nullable();
            $table->string('formula')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('target_value', 18, 4)->nullable();
            $table->decimal('warning_threshold', 18, 4)->nullable();
            $table->decimal('critical_threshold', 18, 4)->nullable();
            $table->string('frequency')->default('monthly');
            $table->string('direction')->default('asc');
            $table->string('category')->nullable();
            $table->boolean('is_template')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['team_id', 'is_active', 'is_template']);
            $table->index(['company_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_kpi_definitions');
    }
};
