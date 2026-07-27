<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timebutler_absence_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('absence_type_id')->constrained('timebutler_absence_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('half_day_start')->default(false);
            $table->boolean('half_day_end')->default(false);
            $table->decimal('total_days', 5, 1)->default(0);
            $table->string('status')->default('pending');
            $table->foreignId('substitute_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('certificate_path')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'user_id', 'status']);
            $table->index(['team_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timebutler_absence_requests');
    }
};
