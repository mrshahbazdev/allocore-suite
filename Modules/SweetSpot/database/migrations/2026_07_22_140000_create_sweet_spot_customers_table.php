<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sweet_spot_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('industry')->nullable();
            $table->string('company_size')->nullable();
            $table->decimal('revenue', 15, 2)->nullable();
            $table->decimal('profit_margin_eur', 15, 2)->nullable();
            $table->decimal('margin_percent', 5, 2)->nullable();
            $table->decimal('effort_hours', 8, 2)->nullable();
            $table->integer('chemistry_score')->nullable();
            $table->integer('growth_score')->nullable();
            $table->decimal('repeat_rate', 5, 2)->nullable();
            $table->integer('recommendations')->nullable();
            $table->integer('payment_willingness')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sweet_spot_customers');
    }
};
