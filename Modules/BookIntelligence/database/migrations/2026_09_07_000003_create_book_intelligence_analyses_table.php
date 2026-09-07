<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookintelligence_book_analyses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')
                ->unique()
                ->constrained('bookintelligence_books')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 24)->default('pending');
            $table->longText('source_material')->nullable();
            $table->string('source_fingerprint', 64)->nullable();
            $table->text('short_summary')->nullable();
            $table->longText('long_summary')->nullable();
            $table->json('key_takeaways')->nullable();
            $table->json('frameworks')->nullable();
            $table->json('actionable_recommendations')->nullable();
            $table->string('provider')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookintelligence_book_analyses');
    }
};
