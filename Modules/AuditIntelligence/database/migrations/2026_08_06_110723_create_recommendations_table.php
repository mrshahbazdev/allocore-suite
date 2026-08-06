<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditintelligence_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('finding_id')->constrained('auditintelligence_findings')->cascadeOnDelete();
            $table->text('issue');
            $table->text('solution')->nullable();
            $table->string('responsible')->nullable();
            $table->enum('effort', ['small', 'medium', 'large'])->default('medium');
            $table->string('related_sop')->nullable();
            $table->enum('status', ['pending', 'accepted', 'implemented', 'dismissed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditintelligence_recommendations');
    }
};
