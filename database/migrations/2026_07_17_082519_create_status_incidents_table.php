<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity')->default('minor');
            $table->string('status')->default('investigating');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['is_resolved', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_incidents');
    }
};
