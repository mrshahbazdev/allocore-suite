<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashcore_liquidity_blockers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('blocker_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('blocked_amount', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('debtor_name')->nullable();
            $table->integer('days_overdue')->default(0);
            $table->string('status')->default('active');
            $table->json('action_items')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashcore_liquidity_blockers');
    }
};
