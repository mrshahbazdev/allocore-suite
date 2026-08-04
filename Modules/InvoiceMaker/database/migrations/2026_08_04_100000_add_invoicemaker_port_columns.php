<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoicemaker_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('team_id')->constrained('users')->nullOnDelete();
            $table->string('logo')->nullable()->after('address');
            $table->text('payment_terms')->nullable()->after('bank_details');
            $table->string('bank_booking_account')->nullable();
            $table->string('cash_booking_account')->nullable();
            $table->string('smtp_host')->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_encryption')->nullable();
            $table->string('smtp_from_address')->nullable();
            $table->string('smtp_from_name')->nullable();
            $table->boolean('smtp_verify_ssl')->default(true);
            $table->string('stripe_account_id')->nullable();
            $table->boolean('stripe_onboarding_complete')->default(false);
            $table->boolean('accept_network_invoices')->default(false);
            $table->string('plan')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
        });

        Schema::table('invoicemaker_templates', function (Blueprint $table) {
            $table->string('logo_position')->nullable()->after('font_family');
            $table->string('signature_path')->nullable();
            $table->boolean('enable_qr')->default(false);
        });

        Schema::table('invoicemaker_expenses', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('category_id')->constrained('invoicemaker_products')->nullOnDelete();
            $table->foreignId('network_invoice_id')->nullable()->after('invoice_id')->constrained('invoicemaker_invoices')->nullOnDelete();
        });

        Schema::table('invoicemaker_clients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('team_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoicemaker_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'logo', 'payment_terms', 'bank_booking_account', 'cash_booking_account',
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption',
                'smtp_from_address', 'smtp_from_name', 'smtp_verify_ssl',
                'stripe_account_id', 'stripe_onboarding_complete', 'accept_network_invoices',
                'plan', 'last_reminder_sent_at',
            ]);
        });

        Schema::table('invoicemaker_templates', function (Blueprint $table) {
            $table->dropColumn(['logo_position', 'signature_path', 'enable_qr']);
        });

        Schema::table('invoicemaker_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropConstrainedForeignId('network_invoice_id');
        });

        Schema::table('invoicemaker_clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
