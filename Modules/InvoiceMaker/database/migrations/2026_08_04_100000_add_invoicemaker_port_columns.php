<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addColumnIfMissing('invoicemaker_profiles', 'user_id', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('team_id')->constrained('users')->nullOnDelete();
        });

        foreach ([
            ['logo', fn (Blueprint $table) => $table->string('logo')->nullable()->after('address')],
            ['payment_terms', fn (Blueprint $table) => $table->text('payment_terms')->nullable()->after('bank_details')],
            ['bank_booking_account', fn (Blueprint $table) => $table->string('bank_booking_account')->nullable()],
            ['cash_booking_account', fn (Blueprint $table) => $table->string('cash_booking_account')->nullable()],
            ['smtp_host', fn (Blueprint $table) => $table->string('smtp_host')->nullable()],
            ['smtp_port', fn (Blueprint $table) => $table->unsignedSmallInteger('smtp_port')->nullable()],
            ['smtp_username', fn (Blueprint $table) => $table->string('smtp_username')->nullable()],
            ['smtp_password', fn (Blueprint $table) => $table->text('smtp_password')->nullable()],
            ['smtp_encryption', fn (Blueprint $table) => $table->string('smtp_encryption')->nullable()],
            ['smtp_from_address', fn (Blueprint $table) => $table->string('smtp_from_address')->nullable()],
            ['smtp_from_name', fn (Blueprint $table) => $table->string('smtp_from_name')->nullable()],
            ['smtp_verify_ssl', fn (Blueprint $table) => $table->boolean('smtp_verify_ssl')->default(true)],
            ['stripe_account_id', fn (Blueprint $table) => $table->string('stripe_account_id')->nullable()],
            ['stripe_onboarding_complete', fn (Blueprint $table) => $table->boolean('stripe_onboarding_complete')->default(false)],
            ['accept_network_invoices', fn (Blueprint $table) => $table->boolean('accept_network_invoices')->default(false)],
            ['plan', fn (Blueprint $table) => $table->string('plan')->nullable()],
            ['last_reminder_sent_at', fn (Blueprint $table) => $table->timestamp('last_reminder_sent_at')->nullable()],
        ] as [$column, $callback]) {
            $this->addColumnIfMissing('invoicemaker_profiles', $column, $callback);
        }

        $this->addColumnIfMissing('invoicemaker_templates', 'logo_position', function (Blueprint $table) {
            $table->string('logo_position')->nullable()->after('font_family');
        });

        $this->addColumnIfMissing('invoicemaker_templates', 'signature_path', function (Blueprint $table) {
            $table->string('signature_path')->nullable();
        });

        $this->addColumnIfMissing('invoicemaker_templates', 'enable_qr', function (Blueprint $table) {
            $table->boolean('enable_qr')->default(false);
        });

        $this->addColumnIfMissing('invoicemaker_expenses', 'product_id', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('category_id')->constrained('invoicemaker_products')->nullOnDelete();
        });

        $this->addColumnIfMissing('invoicemaker_expenses', 'network_invoice_id', function (Blueprint $table) {
            $table->foreignId('network_invoice_id')->nullable()->after('invoice_id')->constrained('invoicemaker_invoices')->nullOnDelete();
        });

        $this->addColumnIfMissing('invoicemaker_clients', 'user_id', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('team_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $this->dropColumnIfExists('invoicemaker_clients', 'user_id');
        $this->dropColumnIfExists('invoicemaker_expenses', 'network_invoice_id');
        $this->dropColumnIfExists('invoicemaker_expenses', 'product_id');
        $this->dropColumnIfExists('invoicemaker_templates', 'enable_qr');
        $this->dropColumnIfExists('invoicemaker_templates', 'signature_path');
        $this->dropColumnIfExists('invoicemaker_templates', 'logo_position');

        foreach ([
            'user_id',
            'logo',
            'payment_terms',
            'bank_booking_account',
            'cash_booking_account',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
            'smtp_from_address',
            'smtp_from_name',
            'smtp_verify_ssl',
            'stripe_account_id',
            'stripe_onboarding_complete',
            'accept_network_invoices',
            'plan',
            'last_reminder_sent_at',
        ] as $column) {
            $this->dropColumnIfExists('invoicemaker_profiles', $column);
        }
    }

    private function addColumnIfMissing(string $table, string $column, callable $callback): void
    {
        if (Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($callback): void {
            $callback($table);
        });
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($column): void {
            if (in_array($column, ['user_id', 'product_id', 'network_invoice_id'], true)) {
                $table->dropConstrainedForeignId($column);
            } else {
                $table->dropColumn($column);
            }
        });
    }
};
