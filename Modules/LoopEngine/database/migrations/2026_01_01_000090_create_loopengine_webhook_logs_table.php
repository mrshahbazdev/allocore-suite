<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loopengine_webhook_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('webhook_id')->constrained('loopengine_webhooks')->cascadeOnDelete();
            $table->string('event');
            $table->json('payload');
            $table->integer('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->boolean('success')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loopengine_webhook_logs');
    }
};
