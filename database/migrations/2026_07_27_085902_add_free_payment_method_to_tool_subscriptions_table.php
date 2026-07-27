<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('tool_subscriptions', function ($table) {
            DB::statement("ALTER TABLE tool_subscriptions MODIFY payment_method ENUM('stripe', 'paypal', 'bank', 'manual', 'trial', 'free')");
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('tool_subscriptions', function ($table) {
            DB::statement("ALTER TABLE tool_subscriptions MODIFY payment_method ENUM('stripe', 'paypal', 'bank', 'manual', 'trial')");
        });
    }
};
