<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tool_subscriptions', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete()->after('plan_id');
            $table->foreignId('tax_rate_id')->nullable()->constrained()->nullOnDelete()->after('coupon_id');
            $table->decimal('subtotal', 10, 2)->nullable()->after('tax_rate_id');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('subtotal');
            $table->decimal('tax_amount', 10, 2)->nullable()->after('discount_amount');
            $table->decimal('total', 10, 2)->nullable()->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('tool_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropForeign(['tax_rate_id']);
            $table->dropColumn(['coupon_id', 'tax_rate_id', 'subtotal', 'discount_amount', 'tax_amount', 'total']);
        });
    }
};
