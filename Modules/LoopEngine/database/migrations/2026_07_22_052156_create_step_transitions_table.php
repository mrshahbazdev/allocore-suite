<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loopengine_step_transitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('step_id')->constrained('loopengine_process_steps')->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('loopengine_step_options')->cascadeOnDelete();
            $table->string('action_type'); // next_step, goto_step, start_process, loop_back, end
            $table->foreignId('target_step_id')->nullable()->constrained('loopengine_process_steps')->nullOnDelete();
            $table->foreignId('target_process_id')->nullable()->constrained('loopengine_processes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loopengine_step_transitions');
    }
};
