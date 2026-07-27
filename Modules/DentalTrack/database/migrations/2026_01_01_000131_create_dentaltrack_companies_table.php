<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dentaltrack_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('address')->nullable();
            $table->json('settings')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['team_id', 'is_active']);
        });

        Schema::create('dentaltrack_labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('dentaltrack_company_id')->index();
            $table->string('name');
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['dentaltrack_company_id', 'is_active']);
        });

        Schema::create('dentaltrack_product_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('dentaltrack_company_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['dentaltrack_company_id', 'is_active']);
        });

        Schema::create('dentaltrack_process_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('dentaltrack_product_type_id')->index();
            $table->unsignedInteger('sort_order');
            $table->string('step_name');
            $table->unsignedInteger('expected_minutes')->nullable();
            $table->timestamps();

            $table->index(['dentaltrack_product_type_id', 'sort_order']);
        });

        Schema::create('dentaltrack_workstations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('dentaltrack_lab_id')->index();
            $table->string('name');
            $table->uuid('qr_code')->unique();
            $table->enum('type', ['station', 'waiting_area'])->default('station');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['dentaltrack_lab_id', 'is_active']);
        });

        Schema::create('dentaltrack_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('dentaltrack_company_id')->index();
            $table->unsignedBigInteger('dentaltrack_lab_id')->index();
            $table->unsignedBigInteger('dentaltrack_product_type_id')->index();
            $table->string('patient_ref')->nullable();
            $table->string('doctor_name')->nullable();
            $table->uuid('qr_code')->unique();
            $table->string('tracking_code', 8)->nullable()->unique();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('predicted_completion_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['dentaltrack_company_id', 'status']);
            $table->index(['dentaltrack_lab_id', 'status']);
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
        });

        Schema::create('dentaltrack_order_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('dentaltrack_order_id')->index();
            $table->unsignedBigInteger('dentaltrack_process_template_id')->nullable()->index();
            $table->unsignedInteger('sort_order');
            $table->string('step_name');
            $table->enum('status', ['pending', 'in_progress', 'done', 'skipped'])->default('pending');
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->timestamps();

            $table->index(['dentaltrack_order_id', 'status']);
            $table->index(['dentaltrack_order_id', 'sort_order']);
        });

        Schema::create('dentaltrack_scan_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('dentaltrack_order_id')->index();
            $table->unsignedBigInteger('dentaltrack_order_step_id')->nullable()->index();
            $table->unsignedBigInteger('dentaltrack_workstation_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->enum('event_type', ['start', 'complete', 'pause', 'transfer_to_waiting']);
            $table->timestamp('scanned_at');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['dentaltrack_order_id', 'event_type']);
            $table->index(['dentaltrack_workstation_id', 'scanned_at']);
            $table->index(['user_id', 'scanned_at']);
        });

        Schema::create('dentaltrack_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('dentaltrack_order_id')->index();
            $table->string('model_version')->default('v2-weighted-avg');
            $table->unsignedInteger('predicted_minutes');
            $table->unsignedInteger('actual_minutes')->nullable();
            $table->decimal('accuracy_pct', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('dentaltrack_rework_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('dentaltrack_order_id')->index();
            $table->unsignedBigInteger('dentaltrack_order_step_id')->index();
            $table->unsignedBigInteger('flagged_by')->index();
            $table->unsignedBigInteger('original_technician')->nullable()->index();
            $table->enum('cause', ['material_defect', 'technique_error', 'equipment_issue', 'design_error', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_rework', 'resolved'])->default('pending');
            $table->unsignedBigInteger('resolved_by')->nullable()->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('cause');
        });

        Schema::create('dentaltrack_qr_print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('printable_type');
            $table->unsignedBigInteger('printable_id');
            $table->enum('format', ['sticker_small', 'sticker_large'])->default('sticker_small');
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();

            $table->index(['printable_type', 'printable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dentaltrack_qr_print_jobs');
        Schema::dropIfExists('dentaltrack_rework_events');
        Schema::dropIfExists('dentaltrack_predictions');
        Schema::dropIfExists('dentaltrack_scan_events');
        Schema::dropIfExists('dentaltrack_order_steps');
        Schema::dropIfExists('dentaltrack_orders');
        Schema::dropIfExists('dentaltrack_workstations');
        Schema::dropIfExists('dentaltrack_process_templates');
        Schema::dropIfExists('dentaltrack_product_types');
        Schema::dropIfExists('dentaltrack_labs');
        Schema::dropIfExists('dentaltrack_companies');
    }
};
