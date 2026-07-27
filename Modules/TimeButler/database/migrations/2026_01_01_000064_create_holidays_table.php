<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timebutler_holidays', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('name');
            $table->string('type')->default('public');
            $table->string('federal_state')->nullable();
            $table->unsignedSmallInteger('year');
            $table->timestamps();

            $table->index(['team_id', 'year', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timebutler_holidays');
    }
};
