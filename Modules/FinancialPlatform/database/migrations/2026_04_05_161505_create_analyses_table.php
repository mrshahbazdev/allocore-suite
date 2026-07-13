<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('financial_companies')->cascadeOnDelete();
            $table->enum('type', ['gmbh', 'jahresabschluss', 'immobilien']);
            $table->string('name');
            $table->enum('status', ['draft', 'complete', 'archived'])->default('draft');
            $table->decimal('total_score', 5, 2)->nullable();
            $table->string('recommendation')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_analyses');
    }
};
