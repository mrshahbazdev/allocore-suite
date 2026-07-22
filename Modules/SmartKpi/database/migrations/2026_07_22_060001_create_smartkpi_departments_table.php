<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_departments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('smartkpi_companies')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('smartkpi_departments')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('industry_type')->nullable();
            $table->string('size')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_departments');
    }
};
