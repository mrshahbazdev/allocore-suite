<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashcore_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('review_type')->default('monthly');
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->json('checklist')->nullable();
            $table->integer('streak_count')->default(0);
            $table->timestamps();

            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashcore_reviews');
    }
};
