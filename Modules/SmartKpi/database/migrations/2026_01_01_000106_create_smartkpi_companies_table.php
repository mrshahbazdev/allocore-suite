<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smartkpi_companies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('industry')->nullable();
            $table->string('size')->nullable();
            $table->string('timezone')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['team_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartkpi_companies');
    }
};
