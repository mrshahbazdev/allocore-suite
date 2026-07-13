<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_kpi_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('tool', 30)->comment('gmbh | jahresabschluss | immobilien');
            $table->string('kpi_code', 30)->comment('EK_QUOTE, QUICK, ROE, etc.');
            $table->string('kpi_name', 100);
            $table->string('unit', 20)->nullable()->comment('%, x, days etc.');
            $table->decimal('green_min', 15, 4)->nullable()->comment('Min value for green');
            $table->decimal('green_max', 15, 4)->nullable()->comment('Max value for green (null = no upper limit)');
            $table->decimal('yellow_min', 15, 4)->nullable();
            $table->decimal('yellow_max', 15, 4)->nullable();
            $table->boolean('lower_is_better')->default(false)->comment('True for DSO, LTV etc.');
            $table->decimal('weight', 5, 2)->nullable()->comment('Scoring weight in %');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tool', 'kpi_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_kpi_thresholds');
    }
};
