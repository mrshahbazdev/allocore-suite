<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoicemaker_accounting_categories')) {
            Schema::table('invoicemaker_accounting_categories', function (Blueprint $table) {
                if (!Schema::hasColumn('invoicemaker_accounting_categories', 'cost_type')) {
                    $table->string('cost_type', 20)->default('variable')->after('type');
                }
            });
        }

        if (Schema::hasTable('invoicemaker_expenses')) {
            Schema::table('invoicemaker_expenses', function (Blueprint $table) {
                if (!Schema::hasColumn('invoicemaker_expenses', 'cost_type')) {
                    $table->string('cost_type', 20)->nullable()->after('category');
                }
            });
        }

        if (Schema::hasTable('invoicemaker_cash_book_entries')) {
            Schema::table('invoicemaker_cash_book_entries', function (Blueprint $table) {
                if (!Schema::hasColumn('invoicemaker_cash_book_entries', 'cost_type')) {
                    $table->string('cost_type', 20)->nullable()->after('type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoicemaker_accounting_categories')) {
            Schema::table('invoicemaker_accounting_categories', function (Blueprint $table) {
                if (Schema::hasColumn('invoicemaker_accounting_categories', 'cost_type')) {
                    $table->dropColumn('cost_type');
                }
            });
        }

        if (Schema::hasTable('invoicemaker_expenses')) {
            Schema::table('invoicemaker_expenses', function (Blueprint $table) {
                if (Schema::hasColumn('invoicemaker_expenses', 'cost_type')) {
                    $table->dropColumn('cost_type');
                }
            });
        }

        if (Schema::hasTable('invoicemaker_cash_book_entries')) {
            Schema::table('invoicemaker_cash_book_entries', function (Blueprint $table) {
                if (Schema::hasColumn('invoicemaker_cash_book_entries', 'cost_type')) {
                    $table->dropColumn('cost_type');
                }
            });
        }
    }
};
