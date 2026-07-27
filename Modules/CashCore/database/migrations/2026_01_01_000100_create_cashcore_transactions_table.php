<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashcore_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cashcore_category_id')->nullable()->constrained('cashcore_categories')->nullOnDelete();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->string('vendor')->nullable();
            $table->date('transaction_date');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_interval')->nullable();
            $table->string('source')->default('manual');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'transaction_date']);
            $table->index(['team_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashcore_transactions');
    }
};
