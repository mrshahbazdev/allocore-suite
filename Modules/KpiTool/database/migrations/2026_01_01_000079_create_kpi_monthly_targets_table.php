<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpitool_kpi_monthly_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_definition_id')->constrained('kpitool_kpi_definitions')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('target_value', 20, 4);
            $table->decimal('growth_rate', 8, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['kpi_definition_id', 'year', 'month']);
            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpitool_kpi_monthly_targets');
    }
};
