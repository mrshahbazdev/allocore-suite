<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timebutler_time_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->unsignedSmallInteger('break_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timebutler_time_entries');
    }
};
