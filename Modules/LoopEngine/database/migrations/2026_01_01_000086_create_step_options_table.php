<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loopengine_step_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('step_id')->constrained('loopengine_process_steps')->cascadeOnDelete();
            $table->string('label_en');
            $table->string('label_de')->nullable();
            $table->string('value');
            $table->unsignedInteger('order')->default(0);
            $table->string('color')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loopengine_step_options');
    }
};
