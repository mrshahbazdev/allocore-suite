<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tool_subscriptions MODIFY payment_method ENUM('stripe', 'paypal', 'bank', 'manual') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tool_subscriptions MODIFY payment_method ENUM('stripe', 'paypal', 'bank') NOT NULL");
        }
    }
};
