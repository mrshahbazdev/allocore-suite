<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bunnyband_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bunnyband_profile_id')->constrained('bunnyband_profiles')->cascadeOnDelete();
            $table->enum('type', ['automatic', 'manual'])->default('automatic');
            $table->string('method');
            $table->string('account_name');
            $table->string('account_number');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bunnyband_payment_methods');
    }
};
