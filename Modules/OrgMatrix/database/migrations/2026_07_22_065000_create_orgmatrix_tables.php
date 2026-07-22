<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orgmatrix_organizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('industry')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['team_id', 'user_id']);
        });

        Schema::create('orgmatrix_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('parent_role_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('department')->nullable();
            $table->string('criticality', 16)->default('medium');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('organization_id')->references('id')->on('orgmatrix_organizations')->cascadeOnDelete();
            $table->foreign('parent_role_id')->references('id')->on('orgmatrix_roles')->nullOnDelete();
            $table->index(['organization_id', 'sort_order']);
        });

        Schema::create('orgmatrix_people', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('organization_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('title')->nullable();
            $table->string('department')->nullable();
            $table->string('avatar')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('organization_id')->references('id')->on('orgmatrix_organizations')->cascadeOnDelete();
            $table->index(['organization_id', 'last_name']);
        });

        Schema::create('orgmatrix_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('person_id');
            $table->boolean('is_primary')->default(false);
            $table->string('succession_horizon', 16)->nullable();
            $table->unsignedTinyInteger('readiness_score')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('orgmatrix_roles')->cascadeOnDelete();
            $table->foreign('person_id')->references('id')->on('orgmatrix_people')->cascadeOnDelete();
            $table->unique(['role_id', 'person_id']);
            $table->index(['team_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orgmatrix_role_assignments');
        Schema::dropIfExists('orgmatrix_people');
        Schema::dropIfExists('orgmatrix_roles');
        Schema::dropIfExists('orgmatrix_organizations');
    }
};
