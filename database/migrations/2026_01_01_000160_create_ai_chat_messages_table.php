<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role');
            $table->longText('content');
            $table->string('module_key')->nullable();
            $table->string('page_url')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
    }
};
