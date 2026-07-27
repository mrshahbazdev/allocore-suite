<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loopengine_process_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('process_id')->constrained('loopengine_processes')->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->text('question_en');
            $table->text('question_de')->nullable();
            $table->text('help_text_en')->nullable();
            $table->text('help_text_de')->nullable();
            $table->string('step_type')->default('question'); // question, decision, loop_check, info, end
            $table->boolean('is_loop_checkpoint')->default(false);
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('max_loops')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loopengine_process_steps');
    }
};
