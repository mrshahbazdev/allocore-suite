<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leadquality_icp_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('industry')->nullable();
            $table->string('employee_count_range')->nullable();
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->string('role')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();

            $table->unique('team_id');
        });

        Schema::create('leadquality_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('status')->default('new');
            $table->string('industry')->nullable();
            $table->string('role')->nullable();
            $table->string('source')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('budget_range')->nullable();
            $table->string('employee_count_range')->nullable();
            $table->unsignedTinyInteger('priority')->default(1);
            $table->timestamp('last_interaction_at')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('ai_high_probability')->default(false);
            $table->string('pipeline_stage')->default('new');
            $table->unsignedSmallInteger('score')->default(0);
            $table->timestamps();

            $table->unique(['team_id', 'email']);
        });

        Schema::create('leadquality_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->constrained('leadquality_contacts')->cascadeOnDelete();
            $table->string('type');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('leadquality_outreach_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('email');
            $table->longText('content');
            $table->timestamps();

            $table->unique(['team_id', 'name']);
        });

        Schema::create('leadquality_sequences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['team_id', 'name']);
        });

        Schema::create('leadquality_sequence_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sequence_id')->constrained('leadquality_sequences')->cascadeOnDelete();
            $table->unsignedInteger('order');
            $table->unsignedInteger('delay_days')->default(0);
            $table->string('subject');
            $table->longText('body');
            $table->timestamps();

            $table->unique(['sequence_id', 'order']);
        });

        Schema::create('leadquality_contact_sequence', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('leadquality_contacts')->cascadeOnDelete();
            $table->foreignId('sequence_id')->constrained('leadquality_sequences')->cascadeOnDelete();
            $table->foreignId('current_step_id')->nullable()->constrained('leadquality_sequence_steps')->nullOnDelete();
            $table->timestamp('next_run_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['contact_id', 'sequence_id']);
        });

        Schema::create('leadquality_email_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email_address');
            $table->string('provider')->nullable();
            $table->string('imap_host')->nullable();
            $table->unsignedInteger('imap_port')->nullable();
            $table->string('imap_encryption')->nullable();
            $table->string('smtp_host')->nullable();
            $table->unsignedInteger('smtp_port')->nullable();
            $table->string('smtp_encryption')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['team_id', 'email_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leadquality_email_accounts');
        Schema::dropIfExists('leadquality_contact_sequence');
        Schema::dropIfExists('leadquality_sequence_steps');
        Schema::dropIfExists('leadquality_sequences');
        Schema::dropIfExists('leadquality_outreach_templates');
        Schema::dropIfExists('leadquality_activities');
        Schema::dropIfExists('leadquality_contacts');
        Schema::dropIfExists('leadquality_icp_profiles');
    }
};
