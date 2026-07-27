<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_forecasts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_definition_id')->constrained('smartkpi_kpi_definitions')->cascadeOnDelete();
            $table->date('forecasted_at');
            $table->string('horizon');
            $table->string('method')->default('linear');
            $table->decimal('value', 18, 4)->nullable();
            $table->decimal('confidence_lower', 18, 4)->nullable();
            $table->decimal('confidence_upper', 18, 4)->nullable();
            $table->timestamps();
            $table->index(['kpi_definition_id', 'forecasted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_forecasts');
    }
};
