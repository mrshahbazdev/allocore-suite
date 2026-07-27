<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_employees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('smartkpi_companies')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('smartkpi_departments')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('role')->default('employee');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['team_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_employees');
    }
};
