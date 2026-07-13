<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('financial_companies')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('position')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('website')->nullable();
            $table->string('source')->default('manual');
            $table->string('status')->default('new');
            $table->string('priority')->default('medium');
            $table->string('industry')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('transferred_to_leados')->default(false);
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_leads');
    }
};
