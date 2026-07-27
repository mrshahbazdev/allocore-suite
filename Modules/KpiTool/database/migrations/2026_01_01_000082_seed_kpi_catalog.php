<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            'Strategisches Management' => [
                ['Umsätze', 'Revenue', 'Gesamtumsatz des Unternehmens', 'Total company revenue', 'Menge x Preis', 'EUR'],
                ['Einkünfte pro Mitarbeiter', 'Revenue per Employee', 'Umsatz geteilt durch Mitarbeiteranzahl', 'Revenue divided by headcount', 'Gesamtumsatz / Anzahl Mitarbeiter', 'EUR'],
                ['Gesamtkosten', 'Total Costs', 'Summe aller Kosten', 'Sum of all costs', 'Fixkosten + variable Kosten', 'EUR'],
                ['Nettoeinkommen', 'Net Income', 'Gewinn nach allen Abzügen', 'Profit after all deductions', 'Umsatz - Kosten - Steuern', 'EUR'],
                ['Gewinnspanne', 'Profit Margin', 'Prozentuale Gewinnmarge', 'Percentage profit margin', '(Gewinn / Umsatz) x 100', '%'],
                ['Betriebsgewinnspanne', 'Operating Profit Margin', 'Betriebliche Gewinnmarge', 'Operating profit as % of revenue', '(Betriebsgewinn / Umsatz) x 100', '%'],
                ['Bruttogewinnspanne', 'Gross Profit Margin', 'Bruttogewinnmarge', 'Gross profit as % of revenue', '(Bruttogewinn / Umsatz) x 100', '%'],
                ['Kundenakquisitionskosten', 'Customer Acquisition Cost', 'Kosten zur Kundengewinnung', 'Cost to acquire one customer', 'Marketing- und Vertriebskosten / neue Kunden', 'EUR'],
                ['Kundenzufriedenheit (NPS)', 'Net Promoter Score', 'Kundenzufriedenheitsbewertung', 'Customer satisfaction score', 'Summe Bewertungen / Anzahl Bewertungen', ''],
                ['Kundenabwanderungsrate', 'Churn Rate', 'Rate der Kundenabwanderung', 'Rate of customer loss', '(verlorene Kunden / Gesamtkunden) x 100', '%'],
            ],
            'Vertrieb' => [
                ['Umsatzwachstum', 'Revenue Growth', 'Umsatzwachstum im Vergleich', 'Revenue growth comparison', '((aktueller - vorheriger) / vorheriger) x 100', '%'],
                ['Lead-Konvertierung', 'Lead Conversion Rate', 'Konvertierungsrate der Leads', 'Lead conversion percentage', '(Conversions / Gesamt) x 100', '%'],
                ['Pipeline-Gesamtwert', 'Pipeline Value', 'Gesamtwert der Verkaufspipeline', 'Total sales pipeline value', 'Summe aller Opportunities', 'EUR'],
                ['Durchschnittlicher Bestellwert', 'Average Order Value', 'Durchschnittlicher Wert pro Bestellung', 'Average value per order', 'Gesamtumsatz / Anzahl Bestellungen', 'EUR'],
                ['Umsatz pro Vertriebsmitarbeiter', 'Revenue per Sales Rep', 'Umsatz je Vertriebsmitarbeiter', 'Revenue per sales representative', 'Gesamtumsatz / Vertriebsmitarbeiter', 'EUR'],
            ],
            'Operative' => [
                ['Auslastung der Arbeitskräfte', 'Workforce Utilization', 'Auslastungsgrad der Mitarbeiter', 'Employee utilization rate', 'geleistete Stunden / verfügbare Stunden x 100', '%'],
                ['Mitarbeiterfluktuationsrate', 'Employee Turnover Rate', 'Rate der Mitarbeiterfluktuation', 'Rate of employee turnover', '(Abgänge / Gesamt) x 100', '%'],
                ['Geldfluss (Cashflow)', 'Cash Flow', 'Geldfluss des Unternehmens', 'Company cash flow', 'Einzahlungen - Auszahlungen', 'EUR'],
                ['Pünktliche Lieferung', 'On-Time Delivery Rate', 'Rate der pünktlichen Lieferung', 'On-time delivery percentage', '(pünktlich / gesamt) x 100', '%'],
                ['Kundenreklamationen', 'Customer Complaints', 'Anzahl der Kundenreklamationen', 'Number of customer complaints', 'Anzahl Reklamationen', ''],
            ],
            'Marketing' => [
                ['Website-Traffic', 'Website Traffic', 'Besucher auf der Website', 'Website visitors', 'Anzahl Besucher', ''],
                ['Konversionsrate', 'Conversion Rate', 'Anteil der Conversions', 'Conversion percentage', '(Conversions / Besucher) x 100', '%'],
                ['Kosten pro Akquisition (CPA)', 'Cost per Acquisition', 'Kosten pro Kundengewinnung', 'Cost per customer acquisition', 'Marketingkosten / Conversions', 'EUR'],
                ['Return on Ad Spend', 'Return on Ad Spend', 'Rendite der Werbeausgaben', 'Return on advertising spend', 'Umsatz / Werbekosten', ''],
                ['E-Mail-Öffnungsrate', 'Email Open Rate', 'Öffnungsrate der E-Mails', 'Email open rate', '(geöffnet / gesendet) x 100', '%'],
            ],
            'Finanzielle' => [
                ['Eigenkapitalrendite (ROE)', 'Return on Equity', 'Rendite des Eigenkapitals', 'Return on equity', 'Nettoeinkommen / Eigenkapital', '%'],
                ['Gesamtkapitalrentabilität (ROA)', 'Return on Assets', 'Rentabilität des Gesamtkapitals', 'Return on total assets', 'Nettoeinkommen / Gesamtvermögen', '%'],
                ['Verschuldungsgrad', 'Debt-to-Equity Ratio', 'Verhältnis Fremd- zu Eigenkapital', 'Debt to equity ratio', 'Fremdkapital / Eigenkapital', ''],
                ['Betriebskapital', 'Working Capital', 'Verfügbares Betriebskapital', 'Available working capital', 'Umlaufvermögen - kurzfristige Verbindlichkeiten', 'EUR'],
                ['Budgetabweichung', 'Budget Variance', 'Abweichung vom Budget', 'Deviation from budget', '(Ist - Plan) / Plan x 100', '%'],
            ],
            'HR' => [
                ['Mitarbeiterzufriedenheit', 'Employee Satisfaction', 'Zufriedenheit der Mitarbeiter', 'Employee satisfaction score', 'Summe Bewertungen / Anzahl', ''],
                ['Kosten pro Einstellung', 'Cost per Hire', 'Kosten pro Neueinstellung', 'Cost per new hire', 'Rekrutierungskosten / Einstellungen', 'EUR'],
                ['Retentionsrate', 'Retention Rate', 'Mitarbeiterbindungsrate', 'Employee retention rate', '(geblieben / gesamt) x 100', '%'],
                ['Abwesenheitsrate', 'Absence Rate', 'Rate der Mitarbeiterabwesenheit', 'Employee absence rate', '(Fehltage / Arbeitstage) x 100', '%'],
                ['Zeit bis zur Besetzung', 'Time to Fill', 'Dauer bis Stellenbesetzung', 'Time to fill a position', 'Durchschnittliche Tage', 'Tage'],
            ],
        ];

        $records = [];
        $now = now();

        foreach ($categories as $category => $kpis) {
            foreach ($kpis as $kpi) {
                $records[] = [
                    'team_id' => null,
                    'user_id' => null,
                    'name_de' => $kpi[0],
                    'name_en' => $kpi[1],
                    'description_de' => $kpi[2],
                    'description_en' => $kpi[3],
                    'formula' => $kpi[4],
                    'unit' => $kpi[5],
                    'category' => $category,
                    'is_template' => true,
                    'is_active' => true,
                    'frequency' => 'monthly',
                    'direction' => 'higher_better',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('kpitool_kpi_definitions')->insert($records);
    }

    public function down(): void
    {
        DB::table('kpitool_kpi_definitions')->where('is_template', true)->delete();
    }
};
