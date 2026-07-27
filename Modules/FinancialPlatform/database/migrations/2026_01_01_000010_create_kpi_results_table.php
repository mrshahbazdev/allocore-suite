<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_kpi_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('analysis_id')->constrained('financial_analyses')->cascadeOnDelete();
            $table->string('kpi_code', 30)->comment('e.g. EK_QUOTE, EBIT_MARGE, DSCR');
            $table->string('kpi_name', 100);
            $table->decimal('value', 20, 6)->nullable()->comment('Computed numeric value');
            $table->decimal('score', 5, 2)->nullable()->comment('0-100 points for this KPI');
            $table->decimal('weight', 5, 2)->nullable()->comment('Weight in %');
            $table->enum('traffic_light', ['green', 'yellow', 'red'])->nullable();
            $table->string('unit', 20)->nullable()->comment('%, x, days, EUR etc.');
            $table->string('year_label', 10)->nullable()->comment('For multi-year analyses');
            $table->timestamps();

            $table->index(['analysis_id', 'kpi_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_kpi_results');
    }
};
