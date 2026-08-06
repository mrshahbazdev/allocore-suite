<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customersuccess_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('question');
            $table->text('answer')->nullable();
            $table->text('problem')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('consequences')->nullable();
            $table->text('recommended_actions')->nullable();
            $table->string('priority')->nullable();
            $table->string('estimated_cost')->nullable();
            $table->string('expected_benefit')->nullable();
            $table->json('sources')->nullable();
            $table->string('module_key')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customersuccess_inquiries');
    }
};
