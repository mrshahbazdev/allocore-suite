<?php

use App\Models\Module;
use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            if (! Schema::hasColumn('modules', 'category')) {
                $table->string('category')->default('Finanzen')->after('description');
            }
            if (! Schema::hasColumn('modules', 'in_subscription_pool')) {
                $table->boolean('in_subscription_pool')->default(true)->after('is_active');
            }
            if (! Schema::hasColumn('modules', 'is_deprecated')) {
                $table->boolean('is_deprecated')->default(false)->after('in_subscription_pool');
            }
            if (! Schema::hasColumn('modules', 'badge_text')) {
                $table->string('badge_text')->nullable()->after('is_deprecated');
            }
            if (! Schema::hasColumn('modules', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('badge_text');
            }
        });

        // 2. Comprehensive Tool Catalog with German Names, Modern Icons, and Categories
        $tools = [
            // Finanzen & Ertrag
            [
                'key' => 'revenue-planner',
                'name' => 'Umsatzplaner',
                'description' => '5-Schritte Finanz- und Umsatzplanung, Fixkosten-Kalkulator, Mindestumsatz & monatliche Zielkontrolle.',
                'icon' => 'chart-bar',
                'category' => 'Finanzen',
                'route_prefix' => 'revenue-planner',
                'badge_text' => 'Kern-Tool',
                'sort_order' => 10,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'cash-core',
                'name' => 'Liquiditäts- & Cash-Radar (CashCore)',
                'description' => 'Profit-First Cash-Transparenz, Kosten-Scoring, Liquiditätswarnung & Gewinnverteilung.',
                'icon' => 'banknotes',
                'category' => 'Finanzen',
                'route_prefix' => 'cashcore',
                'badge_text' => null,
                'sort_order' => 11,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'invoice-maker',
                'name' => 'Rechnungs- & Mahnwesen (InvoiceMaker)',
                'description' => 'Angebote, Rechnungen, automatisiertes Mahnwesen, Kundendaten & Ausgabenverwaltung.',
                'icon' => 'document-text',
                'category' => 'Finanzen',
                'route_prefix' => 'invoices',
                'badge_text' => null,
                'sort_order' => 12,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'kpi-tool',
                'name' => 'KPI-Cockpit & Soll-Ist (KpiTool)',
                'description' => 'Monatlicher Kennzahlen-Katalog, Soll-Ist-Vergleiche, Zielerreichung & Visualisierung.',
                'icon' => 'presentation-chart-line',
                'category' => 'Finanzen',
                'route_prefix' => 'kpitool',
                'badge_text' => null,
                'sort_order' => 13,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'financial-platform',
                'name' => 'Finanz-Plattform (FinancialPlatform)',
                'description' => 'Detaillierte Finanz-KPIs, Umsatzhistorie, Bankabgleich, Budgets & Währungsumrechnung.',
                'icon' => 'building-library',
                'category' => 'Finanzen',
                'route_prefix' => 'finance',
                'badge_text' => null,
                'sort_order' => 14,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],

            // Vertrieb & Marketing
            [
                'key' => 'lead-quality',
                'name' => 'Vertriebs- & Lead-Pipeline (LeadOS)',
                'description' => 'B2B-Leadgenerierung, KI-Scoring, Deal-Phasen & strukturiertes Vertriebs-CRM.',
                'icon' => 'user-group',
                'category' => 'Vertrieb & Marketing',
                'route_prefix' => 'leads',
                'badge_text' => 'Vertrieb',
                'sort_order' => 20,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'sweet-spot',
                'name' => 'Kundenwert- & Rentabilitäts-Analyse (SweetSpot)',
                'description' => 'A-Kunden identifizieren, Margenpotenziale heben & lukrative Zielgruppen fokussieren.',
                'icon' => 'sparkles',
                'category' => 'Vertrieb & Marketing',
                'route_prefix' => 'sweetspot',
                'badge_text' => null,
                'sort_order' => 21,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'keyword-cluster',
                'name' => 'Content- & Keyword-Cluster (ClusterForge)',
                'description' => 'KI-gestützte Themencluster, SEO-Suchbegriffe & gezielte Marketingkampagnen.',
                'icon' => 'magnifying-glass',
                'category' => 'Vertrieb & Marketing',
                'route_prefix' => 'clusters',
                'badge_text' => null,
                'sort_order' => 22,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],

            // Produktivität & Prozesse
            [
                'key' => 'plan-hive',
                'name' => 'Projekt- & Aufgaben-Hub (PlanHive)',
                'description' => 'Aufgabenmanagement, Projekt-Meilensteine, Team-Kalender, Kontakte & Dokumente.',
                'icon' => 'folder',
                'category' => 'Produktivität & Prozesse',
                'route_prefix' => 'planhive',
                'badge_text' => 'Beliebt',
                'sort_order' => 30,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'loop-engine',
                'name' => 'Prozess- & SOP-Engine (LoopEngine)',
                'description' => 'Schritt-für-Schritt SOPs, Qualitäts-Checks, Prüfschleifen & lückenlose Audit-Trails.',
                'icon' => 'arrow-path',
                'category' => 'Produktivität & Prozesse',
                'route_prefix' => 'loopengine',
                'badge_text' => null,
                'sort_order' => 31,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'sop-builder',
                'name' => 'SOP- & Checklisten-Generator (SopBuilder)',
                'description' => 'Erstellt strukturierte Checklisten, Handbücher, Schulungsmaterialien & Audit-Nachweise.',
                'icon' => 'clipboard-document-check',
                'category' => 'Produktivität & Prozesse',
                'route_prefix' => 'sopbuilder',
                'badge_text' => null,
                'sort_order' => 32,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'time-butler',
                'name' => 'Zeiterfassung & Urlaubsplaner (TimeButler)',
                'description' => 'Arbeitszeitbuchung, Abwesenheiten, Urlaubsanträge & übersichtlicher Teamkalender.',
                'icon' => 'clock',
                'category' => 'Produktivität & Prozesse',
                'route_prefix' => 'timebutler',
                'badge_text' => null,
                'sort_order' => 33,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'smart-kpi',
                'name' => 'Ziel- & Performance-Manager (SmartKPI)',
                'description' => 'Hierarchische Unternehmensziele, Abteilungs-KPIs, Maßnahmen & Prognosen.',
                'icon' => 'target-arrow',
                'category' => 'Produktivität & Prozesse',
                'route_prefix' => 'smartkpi',
                'badge_text' => null,
                'sort_order' => 34,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],

            // Führung & Strategie
            [
                'key' => 'audit',
                'name' => 'AuditPro (Unternehmens-Reifegrad)',
                'description' => 'Diagnostischer 5-Säulen-Check, Radar-Auswertung, Stärken-Analyse & Allocore Coach.',
                'icon' => 'shield-check',
                'category' => 'Führung & Strategie',
                'route_prefix' => 'audit',
                'badge_text' => 'Strategie',
                'sort_order' => 40,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'focus-matrix',
                'name' => 'Fokus- & Prioritäten-Matrix (FocusMatrix)',
                'description' => 'Aufgaben triagieren, delegieren oder eliminieren für maximale Unternehmer-Produktivität.',
                'icon' => 'compass',
                'category' => 'Führung & Strategie',
                'route_prefix' => 'focusmatrix',
                'badge_text' => null,
                'sort_order' => 41,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'org-matrix',
                'name' => 'Organigramm & Rollen-Matrix (OrgMatrix)',
                'description' => 'Unternehmensstrukturen visualisieren, Rollen definieren, Aufgaben zuweisen & Nachfolge planen.',
                'icon' => 'user-group',
                'category' => 'Führung & Strategie',
                'route_prefix' => 'orgmatrix',
                'badge_text' => null,
                'sort_order' => 42,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'vision-flow',
                'name' => 'Vision- & Strategie-Planer (VisionFlow)',
                'description' => 'Unternehmenswerte, Leitbild, Prinzipien & strategische Meilensteine gemeinsam erarbeiten.',
                'icon' => 'rocket-launch',
                'category' => 'Führung & Strategie',
                'route_prefix' => 'visionflow',
                'badge_text' => null,
                'sort_order' => 43,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'nur-du',
                'name' => 'Führungs- & Ausrichtungs-OS (Nur-Du)',
                'description' => 'Leitplanken definieren, Quartalsfokus festlegen & strategische Führungsentscheidungen absichern.',
                'icon' => 'star',
                'category' => 'Führung & Strategie',
                'route_prefix' => 'nurdu',
                'badge_text' => null,
                'sort_order' => 44,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],

            // Wissen & Bildung
            [
                'key' => 'book-intelligence',
                'name' => 'Wissens- & Fachbuch-Bibliothek',
                'description' => 'Strukturierte Zusammenfassungen relevanter Fachbücher, Kern-Learnings & gezielte Lernpfade.',
                'icon' => 'book-open',
                'category' => 'Wissen & Bildung',
                'route_prefix' => 'books',
                'badge_text' => 'Wissen',
                'sort_order' => 50,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'knowledge-manager',
                'name' => 'Unternehmens-Wissensdatenbank',
                'description' => 'Betriebswissen, Prozesse, Architektur und Richtlinien erfassen und für das Team aufbereiten.',
                'icon' => 'lightbulb',
                'category' => 'Wissen & Bildung',
                'route_prefix' => 'knowledge',
                'badge_text' => null,
                'sort_order' => 51,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],

            // Spezial- & Assistenz-Module
            [
                'key' => 'dental-track',
                'name' => 'DentalTrack (Labor-Produktion)',
                'description' => 'QR-gestützte Produktionserfassung für Dentallabore mit Arbeitsplatz-Scans und Statusverfolgung.',
                'icon' => 'qr-code',
                'category' => 'Produktivität & Prozesse',
                'route_prefix' => 'dentaltrack',
                'badge_text' => null,
                'sort_order' => 60,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'dev-manager',
                'name' => 'SaaS Development Manager',
                'description' => 'Anforderungsmanagement, User Stories und Release-Planung für digitale Produkte.',
                'icon' => 'code-bracket',
                'category' => 'Produktivität & Prozesse',
                'route_prefix' => 'dev',
                'badge_text' => null,
                'sort_order' => 61,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'audit-intelligence',
                'name' => 'Audit Intelligence Assistant',
                'description' => 'Analysiert Audit-Befunde, generiert Handlungsempfehlungen und Potenziale.',
                'icon' => 'shield-check',
                'category' => 'Führung & Strategie',
                'route_prefix' => 'audit-intelligence',
                'badge_text' => null,
                'sort_order' => 62,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
            [
                'key' => 'customer-success',
                'name' => 'Customer Success Assistant',
                'description' => 'KI-gestützter Kundenerfolgs-Assistent mit Ursachenanalyse und Maßnahmenplänen.',
                'icon' => 'chat-bubble-left-right',
                'category' => 'Vertrieb & Marketing',
                'route_prefix' => 'customer-success',
                'badge_text' => null,
                'sort_order' => 63,
                'in_subscription_pool' => true,
                'is_active' => true,
            ],
        ];

        foreach ($tools as $tool) {
            Module::updateOrCreate(['key' => $tool['key']], $tool);
        }

        // 3. Sync all active pool tools to the main 'All Tools Bundle' plan
        $allToolsPlan = Plan::where('slug', 'all-tools')->first();
        if ($allToolsPlan) {
            $poolModuleIds = Module::where('in_subscription_pool', true)->where('is_active', true)->pluck('id')->all();
            $allToolsPlan->modules()->sync($poolModuleIds);
        }
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['category', 'in_subscription_pool', 'is_deprecated', 'badge_text', 'sort_order']);
        });
    }
};
