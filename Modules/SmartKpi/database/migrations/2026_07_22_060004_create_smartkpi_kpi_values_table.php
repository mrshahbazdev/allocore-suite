<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_kpi_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_definition_id')->constrained('smartkpi_kpi_definitions')->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('value', 18, 4);
            $table->date('recorded_at');
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['kpi_definition_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_kpi_values');
    }
};
