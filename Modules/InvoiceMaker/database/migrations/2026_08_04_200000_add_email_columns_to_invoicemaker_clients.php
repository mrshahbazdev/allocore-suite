<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('invoicemaker_clients', 'email_subject')) {
            Schema::table('invoicemaker_clients', function (Blueprint $table): void {
                $table->string('email_subject')->nullable()->after('language');
            });
        }

        if (! Schema::hasColumn('invoicemaker_clients', 'email_template')) {
            Schema::table('invoicemaker_clients', function (Blueprint $table): void {
                $table->text('email_template')->nullable()->after('email_subject');
            });
        }
    }

    public function down(): void
    {
        Schema::table('invoicemaker_clients', function (Blueprint $table): void {
            $table->dropColumn(['email_subject', 'email_template']);
        });
    }
};
