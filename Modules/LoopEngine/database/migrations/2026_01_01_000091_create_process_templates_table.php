<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loopengine_process_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('process_id')->constrained('loopengine_processes')->cascadeOnDelete();
            $table->foreignId('shared_by')->constrained('users')->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_de')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_de')->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_public')->default(true);
            $table->integer('install_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loopengine_process_templates');
    }
};
