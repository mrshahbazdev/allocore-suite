<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planhive_reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('planhive_projects')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('remindable');
            $table->string('title');
            $table->timestamp('remind_at');
            $table->boolean('is_done')->default(false);
            $table->timestamps();

            $table->index(['team_id', 'user_id', 'remind_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planhive_reminders');
    }
};
