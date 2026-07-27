<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timebutler_absence_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('absence_request_id')->constrained('timebutler_absence_requests')->cascadeOnDelete();
            $table->date('date');
            $table->boolean('half_day')->default(false);
            $table->timestamps();

            $table->index(['absence_request_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timebutler_absence_days');
    }
};
