<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_alert_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_definition_id')->constrained('smartkpi_kpi_definitions')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('smartkpi_companies')->cascadeOnDelete();
            $table->string('threshold_type');
            $table->decimal('threshold_value', 18, 4)->nullable();
            $table->string('severity')->default('warning');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['kpi_definition_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_alert_rules');
    }
};
