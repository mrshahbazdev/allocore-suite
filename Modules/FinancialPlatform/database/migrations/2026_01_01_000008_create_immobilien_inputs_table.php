<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_immobilien_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('analysis_id')->constrained('financial_analyses')->cascadeOnDelete();

            // Purchase
            $table->decimal('purchase_price', 15, 2)->nullable()->comment('Kaufpreis');
            $table->decimal('closing_costs', 15, 2)->nullable()->comment('Nebenkosten (Notar, Grunderwerbsteuer, Makler)');
            $table->decimal('renovation_costs', 15, 2)->nullable()->default(0)->comment('Renovierungskosten');

            // Property details
            $table->decimal('area_sqm', 10, 2)->nullable()->comment('Wohnfläche in qm');
            $table->string('property_type', 50)->nullable()->comment('Wohnhaus, Mehrfamilienhaus, Gewerbe, etc.');
            $table->string('location', 255)->nullable()->comment('Adresse / Lage');

            // Rental income
            $table->decimal('rent_net', 15, 2)->nullable()->comment('Monatliche Nettokaltmiete (aktuell)');
            $table->decimal('market_rent', 15, 2)->nullable()->comment('Marktmiete (Potential)');
            $table->decimal('vacancy_rate', 5, 2)->nullable()->default(5)->comment('Leerstandsquote in %');
            $table->decimal('management_costs_pct', 5, 2)->nullable()->default(10)->comment('Bewirtschaftungskosten in % der Bruttomiete');

            // Financing
            $table->decimal('equity', 15, 2)->nullable()->comment('Eigenkapital');
            $table->decimal('loan_rate', 5, 2)->nullable()->comment('Zinssatz in % p.a.');
            $table->decimal('repayment_rate', 5, 2)->nullable()->comment('Tilgungsrate in % p.a.');
            $table->integer('loan_term_years')->nullable()->default(20)->comment('Kreditlaufzeit in Jahren');

            // Qualitative scores (1-10)
            $table->unsignedTinyInteger('location_score')->nullable()->comment('Lage-Score 1-10');
            $table->unsignedTinyInteger('condition_score')->nullable()->comment('Zustand-Score 1-10');
            $table->unsignedTinyInteger('rent_growth_score')->nullable()->comment('Mietsteigerungspotenzial 1-10');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_immobilien_inputs');
    }
};
