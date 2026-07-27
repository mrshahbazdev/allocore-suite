<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('financial_gmbh_inputs', 'custom_weights')) {
            Schema::table('financial_gmbh_inputs', function (Blueprint $table): void {
                $table->json('custom_weights')->nullable()->after('market_score');
            });
        }

        if (! Schema::hasColumn('financial_immobilien_inputs', 'custom_weights')) {
            Schema::table('financial_immobilien_inputs', function (Blueprint $table): void {
                $table->json('custom_weights')->nullable()->after('rent_growth_score');
            });
        }
    }

    public function down(): void
    {
        Schema::table('financial_gmbh_inputs', function (Blueprint $table): void {
            $table->dropColumn('custom_weights');
        });

        Schema::table('financial_immobilien_inputs', function (Blueprint $table): void {
            $table->dropColumn('custom_weights');
        });
    }
};
