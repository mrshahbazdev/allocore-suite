<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashcore_profit_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('bucket');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('allocated_amount', 12, 2)->default(0);
            $table->string('period')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashcore_profit_allocations');
    }
};
