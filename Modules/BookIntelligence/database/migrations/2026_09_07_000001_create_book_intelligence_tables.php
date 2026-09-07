<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookintelligence_authors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('bio')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'name']);
        });

        Schema::create('bookintelligence_publishers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('website')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'name']);
        });

        Schema::create('bookintelligence_topics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('bookintelligence_topics')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'slug']);
            $table->index(['team_id', 'parent_id']);
        });

        Schema::create('bookintelligence_books', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('bookintelligence_authors')->nullOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('bookintelligence_publishers')->nullOnDelete();
            $table->foreignId('main_topic_id')->nullable()->constrained('bookintelligence_topics')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('isbn', 32)->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->string('language', 12)->default('en');
            $table->string('difficulty', 16)->default('intermediate');
            $table->string('cover_url')->nullable();
            $table->text('description')->nullable();
            $table->json('relevant_roles')->nullable();
            $table->string('affiliate_link')->nullable();
            $table->string('status', 16)->default('active');
            $table->timestamps();

            $table->unique(['team_id', 'slug']);
            $table->index(['team_id', 'title']);
            $table->index(['team_id', 'difficulty']);
        });

        Schema::create('bookintelligence_book_topic', function (Blueprint $table): void {
            $table->foreignId('book_id')->constrained('bookintelligence_books')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('bookintelligence_topics')->cascadeOnDelete();

            $table->primary(['book_id', 'topic_id']);
        });

        Schema::create('bookintelligence_reading_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('bookintelligence_books')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 16)->default('planned');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['book_id', 'user_id']);
            $table->index(['team_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookintelligence_reading_progress');
        Schema::dropIfExists('bookintelligence_book_topic');
        Schema::dropIfExists('bookintelligence_books');
        Schema::dropIfExists('bookintelligence_topics');
        Schema::dropIfExists('bookintelligence_publishers');
        Schema::dropIfExists('bookintelligence_authors');
    }
};
