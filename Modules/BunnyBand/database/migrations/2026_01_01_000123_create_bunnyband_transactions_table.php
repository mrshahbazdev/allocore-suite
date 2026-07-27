<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bunnyband_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bunnyband_profile_id')->constrained('bunnyband_profiles')->cascadeOnDelete();
            $table->enum('type', ['welcome_bonus', 'task_earning', 'referral_earning', 'withdrawal', 'deposit']);
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->string('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_proof')->nullable();
            $table->string('screenshot')->nullable();
            $table->unsignedBigInteger('bunnyband_withdrawal_method_id')->nullable()->index();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bunnyband_transactions');
    }
};
