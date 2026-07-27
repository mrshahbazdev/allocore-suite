<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_kpi_relationships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cause_kpi_id')->constrained('smartkpi_kpi_definitions')->cascadeOnDelete();
            $table->foreignId('effect_kpi_id')->constrained('smartkpi_kpi_definitions')->cascadeOnDelete();
            $table->integer('lag_periods')->nullable();
            $table->decimal('correlation', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['cause_kpi_id', 'effect_kpi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_kpi_relationships');
    }
};
