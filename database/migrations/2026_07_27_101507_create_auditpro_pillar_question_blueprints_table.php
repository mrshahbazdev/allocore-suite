<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditpro_pillar_question_blueprints', function (Blueprint $table) {
            $table->id();
            $table->string('pillar');
            $table->unsignedTinyInteger('position');
            $table->text('question');
            $table->text('description')->nullable();
            $table->text('recommendation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['pillar', 'position'], 'idx_apqbp_pillar_position');
            $table->index('pillar', 'idx_apqbp_pillar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditpro_pillar_question_blueprints');
    }
};
