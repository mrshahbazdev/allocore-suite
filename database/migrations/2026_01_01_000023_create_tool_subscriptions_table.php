<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->morphs('billable'); // User or Team
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->enum('payment_method', ['stripe', 'paypal', 'bank']);
            $table->enum('billing_interval', ['monthly', 'yearly'])->default('monthly');
            $table->enum('status', ['pending', 'active', 'cancelled', 'expired', 'rejected'])->default('pending');
            $table->string('gateway_reference')->nullable(); // Stripe sub id / PayPal sub id / bank ref
            $table->string('receipt_path')->nullable(); // bank transfer proof upload
            $table->text('admin_note')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_subscriptions');
    }
};
