<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_jahresabschluss_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('analysis_id')->constrained('financial_analyses')->cascadeOnDelete();
            $table->string('year_label', 10)->comment('e.g. 2022, 2023, 2024');
            $table->unsignedTinyInteger('year_order')->default(1)->comment('1=oldest, 3=newest');

            // Bilanz — Aktiva (Assets)
            $table->decimal('cash', 15, 2)->nullable()->comment('Kassenbestand');
            $table->decimal('receivables', 15, 2)->nullable()->comment('Forderungen aus L+L');
            $table->decimal('inventory', 15, 2)->nullable()->comment('Vorräte');
            $table->decimal('other_current_assets', 15, 2)->nullable()->comment('Sonstige kurzfristige Aktiva');
            $table->decimal('current_assets', 15, 2)->nullable()->comment('Umlaufvermögen gesamt');
            $table->decimal('fixed_assets', 15, 2)->nullable()->comment('Anlagevermögen');
            $table->decimal('total_assets', 15, 2)->nullable()->comment('Bilanzsumme');

            // Bilanz — Passiva (Liabilities)
            $table->decimal('equity', 15, 2)->nullable()->comment('Eigenkapital');
            $table->decimal('current_liabilities', 15, 2)->nullable()->comment('Kurzfristige Verbindlichkeiten');
            $table->decimal('long_term_liabilities', 15, 2)->nullable()->comment('Langfristige Verbindlichkeiten');
            $table->decimal('total_liabilities', 15, 2)->nullable()->comment('Verbindlichkeiten gesamt');
            $table->decimal('payables', 15, 2)->nullable()->comment('Verbindlichkeiten aus L+L');

            // GuV (Profit & Loss)
            $table->decimal('revenue', 15, 2)->nullable()->comment('Umsatzerlöse');
            $table->decimal('material_costs', 15, 2)->nullable()->comment('Materialaufwand');
            $table->decimal('personnel_costs', 15, 2)->nullable()->comment('Personalaufwand');
            $table->decimal('depreciation', 15, 2)->nullable()->comment('Abschreibungen');
            $table->decimal('other_opex', 15, 2)->nullable()->comment('Sonstige betriebliche Aufwendungen');
            $table->decimal('ebit', 15, 2)->nullable()->comment('EBIT');
            $table->decimal('interest_exp', 15, 2)->nullable()->comment('Zinsaufwand');
            $table->decimal('ebt', 15, 2)->nullable()->comment('EBT');
            $table->decimal('taxes', 15, 2)->nullable()->comment('Steuern');
            $table->decimal('net_profit', 15, 2)->nullable()->comment('Jahresüberschuss');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_jahresabschluss_inputs');
    }
};
