<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('blog_comments')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('website')->nullable();
            $table->text('body');
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index(['post_id', 'is_approved', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
    }
};
