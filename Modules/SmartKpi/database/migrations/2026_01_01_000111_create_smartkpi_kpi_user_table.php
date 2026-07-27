<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_kpi_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kpi_definition_id')->constrained('smartkpi_kpi_definitions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('viewer');
            $table->timestamps();
            $table->unique(['kpi_definition_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_kpi_user');
    }
};
