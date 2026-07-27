<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashcore_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('alert_type');
            $table->string('severity')->default('info');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_dismissed')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashcore_alerts');
    }
};
