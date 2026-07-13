<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_gmbh_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('analysis_id')->constrained('financial_analyses')->cascadeOnDelete();
            // Revenue
            $table->decimal('revenue_current', 15, 2)->nullable()->comment('Umsatz aktuelles Jahr');
            $table->decimal('revenue_prev', 15, 2)->nullable()->comment('Umsatz Vorjahr');
            // Costs
            $table->decimal('cogs', 15, 2)->nullable()->comment('Herstellungskosten / COGS');
            $table->decimal('personnel', 15, 2)->nullable()->comment('Personalkosten');
            $table->decimal('other_opex', 15, 2)->nullable()->comment('Sonstige Betriebsausgaben');
            // Profitability
            $table->decimal('ebitda', 15, 2)->nullable()->comment('EBITDA');
            $table->decimal('depreciation', 15, 2)->nullable()->comment('Abschreibungen');
            $table->decimal('interest', 15, 2)->nullable()->comment('Zinsaufwand');
            $table->decimal('net_profit', 15, 2)->nullable()->comment('Jahresüberschuss');
            // Balance Sheet
            $table->decimal('total_assets', 15, 2)->nullable()->comment('Bilanzsumme');
            $table->decimal('equity', 15, 2)->nullable()->comment('Eigenkapital');
            $table->decimal('total_debt', 15, 2)->nullable()->comment('Gesamtverbindlichkeiten');
            $table->decimal('current_liabilities', 15, 2)->nullable()->comment('Kurzfristige Verbindlichkeiten');
            $table->decimal('current_assets', 15, 2)->nullable()->comment('Umlaufvermögen');
            $table->decimal('cash', 15, 2)->nullable()->comment('Kassenbestand / Liquidität');
            $table->decimal('monthly_burn', 15, 2)->nullable()->comment('Monatlicher Cashburn');
            // Customer metrics
            $table->decimal('cac', 15, 2)->nullable()->comment('Customer Acquisition Cost');
            $table->decimal('ltv', 15, 2)->nullable()->comment('Lifetime Value');
            // Qualitative scores (1-10)
            $table->unsignedTinyInteger('mgmt_score')->nullable()->comment('Management-Score 1-10');
            $table->unsignedTinyInteger('market_score')->nullable()->comment('Markt & Wettbewerb Score 1-10');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_gmbh_inputs');
    }
};
