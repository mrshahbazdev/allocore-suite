<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timebutler_vacation_balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('total_days', 5, 1)->default(0);
            $table->decimal('taken_days', 5, 1)->default(0);
            $table->decimal('requested_days', 5, 1)->default(0);
            $table->decimal('remaining_days', 5, 1)->default(0);
            $table->timestamps();

            $table->unique(['team_id', 'user_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timebutler_vacation_balances');
    }
};
