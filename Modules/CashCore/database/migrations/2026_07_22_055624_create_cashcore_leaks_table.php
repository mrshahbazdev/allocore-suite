<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashcore_leaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cashcore_transaction_id')->nullable()->constrained('cashcore_transactions')->nullOnDelete();
            $table->string('leak_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('monthly_amount', 12, 2)->default(0);
            $table->integer('leak_score')->default(0);
            $table->string('status')->default('detected');
            $table->text('recommendation')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashcore_leaks');
    }
};
