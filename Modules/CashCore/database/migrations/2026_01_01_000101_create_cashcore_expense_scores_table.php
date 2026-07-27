<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashcore_expense_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cashcore_transaction_id')->constrained('cashcore_transactions')->cascadeOnDelete();
            $table->string('purpose')->nullable();
            $table->string('benefit')->nullable();
            $table->integer('revenue_score')->default(0);
            $table->integer('efficiency_score')->default(0);
            $table->integer('strategic_score')->default(0);
            $table->integer('usage_score')->default(0);
            $table->integer('total_score')->default(0);
            $table->string('recommendation')->default('keep');
            $table->timestamps();

            $table->unique('cashcore_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashcore_expense_scores');
    }
};
