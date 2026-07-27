<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoicemaker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('timezone')->default('UTC');
            $table->text('bank_details')->nullable();
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->string('invoice_number_prefix')->default('INV');
            $table->unsignedBigInteger('invoice_number_next')->default(1);
            $table->string('estimate_number_prefix')->default('EST');
            $table->unsignedBigInteger('estimate_number_next')->default(1);
            $table->string('booking_number_prefix')->default('BOOK');
            $table->unsignedBigInteger('booking_number_next')->default(1);
            $table->unsignedInteger('payment_terms_days')->default(14);
            $table->text('default_payment_terms')->nullable();
            $table->decimal('late_fee_percentage', 5, 2)->default(0);
            $table->boolean('enable_automated_reminders')->default(false);
            $table->unsignedInteger('reminder_days_interval')->default(7);
            $table->string('stripe_public_key')->nullable();
            $table->text('stripe_secret_key')->nullable();
            $table->timestamps();
        });

        Schema::create('invoicemaker_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('language', 5)->default('en');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'name']);
        });

        Schema::create('invoicemaker_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->string('unit')->default('unit');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('manage_stock')->default(false);
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['team_id', 'name']);
        });

        Schema::create('invoicemaker_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->string('primary_color', 20)->default('#4f46e5');
            $table->string('font_family')->default('DejaVu Sans');
            $table->string('header_style')->default('simple');
            $table->text('payment_terms')->nullable();
            $table->text('footer_message')->nullable();
            $table->boolean('show_tax')->default(true);
            $table->boolean('show_discount')->default(true);
            $table->timestamps();
        });

        Schema::create('invoicemaker_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_id')->constrained('invoicemaker_clients')->restrictOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('invoicemaker_templates')->nullOnDelete();
            $table->string('invoice_number');
            $table->string('type')->default('invoice');
            $table->string('status')->default('draft');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('currency', 3)->default('EUR');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('late_fee_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('amount_due', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('payment_terms')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();
            $table->date('next_run_date')->nullable();
            $table->date('last_run_date')->nullable();
            $table->timestamp('scheduled_send_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->timestamp('public_viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revision_requested_at')->nullable();
            $table->boolean('inventory_deducted')->default(false);
            $table->timestamps();
            $table->unique(['team_id', 'invoice_number']);
            $table->index(['team_id', 'type', 'status']);
        });

        Schema::create('invoicemaker_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoicemaker_invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('invoicemaker_products')->nullOnDelete();
            $table->text('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('invoicemaker_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoicemaker_invoices')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method');
            $table->date('date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoicemaker_accounting_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('expense');
            $table->string('booking_account')->nullable();
            $table->string('posting_rule')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'name', 'type']);
        });

        Schema::create('invoicemaker_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('invoicemaker_accounting_categories')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('invoicemaker_clients')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoicemaker_invoices')->nullOnDelete();
            $table->string('category')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('partner_name')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'date']);
        });

        Schema::create('invoicemaker_cash_book_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('invoicemaker_accounting_categories')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoicemaker_invoices')->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained('invoicemaker_expenses')->nullOnDelete();
            $table->string('booking_number');
            $table->string('reference_number')->nullable();
            $table->date('date');
            $table->date('document_date')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('type');
            $table->string('source')->nullable();
            $table->string('partner_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'booking_number']);
            $table->index(['team_id', 'date']);
        });

        Schema::create('invoicemaker_invoice_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoicemaker_invoices')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_name')->nullable();
            $table->text('comment');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });

        Schema::create('invoicemaker_email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('invoice');
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('invoicemaker_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoicemaker_invoices')->nullOnDelete();
            $table->string('recipient_email');
            $table->string('subject');
            $table->string('type')->default('manual');
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoicemaker_email_logs');
        Schema::dropIfExists('invoicemaker_email_templates');
        Schema::dropIfExists('invoicemaker_invoice_comments');
        Schema::dropIfExists('invoicemaker_cash_book_entries');
        Schema::dropIfExists('invoicemaker_expenses');
        Schema::dropIfExists('invoicemaker_accounting_categories');
        Schema::dropIfExists('invoicemaker_payments');
        Schema::dropIfExists('invoicemaker_invoice_items');
        Schema::dropIfExists('invoicemaker_invoices');
        Schema::dropIfExists('invoicemaker_templates');
        Schema::dropIfExists('invoicemaker_products');
        Schema::dropIfExists('invoicemaker_clients');
        Schema::dropIfExists('invoicemaker_profiles');
    }
};
