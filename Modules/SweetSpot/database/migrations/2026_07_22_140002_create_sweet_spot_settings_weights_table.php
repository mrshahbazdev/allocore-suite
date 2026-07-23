<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sweet_spot_settings_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('criterion_key');
            $table->integer('weight')->default(1);
            $table->timestamps();

            $table->unique(['team_id', 'criterion_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sweet_spot_settings_weights');
    }
};
