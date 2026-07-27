<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->timestamp('custom_domain_verified_at')->nullable()->after('custom_domain');
            $table->string('ssl_status')->default('pending')->after('custom_domain_verified_at');
            $table->timestamp('ssl_issued_at')->nullable()->after('ssl_status');
            $table->timestamp('ssl_expires_at')->nullable()->after('ssl_issued_at');
            $table->text('ssl_last_error')->nullable()->after('ssl_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['custom_domain_verified_at', 'ssl_status', 'ssl_issued_at', 'ssl_expires_at', 'ssl_last_error']);
        });
    }
};
