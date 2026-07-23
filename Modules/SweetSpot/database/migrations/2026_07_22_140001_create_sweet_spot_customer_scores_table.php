<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sweet_spot_customer_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('sweet_spot_customers')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->decimal('margin_per_hour', 15, 2)->nullable();
            $table->decimal('profitability_score', 8, 2)->nullable();
            $table->decimal('effort_score', 8, 2)->nullable();
            $table->decimal('chemistry_score', 8, 2)->nullable();
            $table->decimal('growth_score', 8, 2)->nullable();
            $table->decimal('repeat_score', 8, 2)->nullable();
            $table->decimal('recommendation_score', 8, 2)->nullable();
            $table->decimal('payment_score', 8, 2)->nullable();
            $table->decimal('total_score', 8, 2)->nullable();
            $table->integer('rank')->nullable();
            $table->boolean('top_flag')->default(false);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id']);
            $table->index(['team_id', 'total_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sweet_spot_customer_scores');
    }
};
