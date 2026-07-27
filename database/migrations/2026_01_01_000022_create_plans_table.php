<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->string('stripe_price_id_monthly')->nullable();
            $table->string('stripe_price_id_yearly')->nullable();
            $table->string('paypal_plan_id_monthly')->nullable();
            $table->string('paypal_plan_id_yearly')->nullable();
            $table->enum('billable_scope', ['user', 'team', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('module_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['plan_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_plan');
        Schema::dropIfExists('plans');
    }
};
