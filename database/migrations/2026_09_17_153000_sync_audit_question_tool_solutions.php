<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mappings = [
            'The required monthly revenue is defined and realistically planned.' => [
                'module_key' => 'revenue-planner',
                'knowledge_slug' => 'fixkosten',
                'recommendation' => "1. Erfasse alle monatlichen Fixkosten.\n2. Ermittle die variablen Kosten Deiner Leistungen oder Produkte.\n3. Berücksichtige bekannte Kostensteigerungen.\n4. Berechne daraus den notwendigen Mindestumsatz.\n5. Plane diesen Umsatz auf Monate und Quartale.\n6. Vergleiche regelmäßig Planung und Realität.",
            ],
            'Suitable prospects are reached continuously.' => [
                'module_key' => 'keyword-cluster',
                'knowledge_slug' => 'revenue',
                'recommendation' => "1. Definiere Deine Zielkunden.\n2. Notiere, über welche Wege diese erreichbar sind.\n3. Plane regelmäßige Marketing- oder Vertriebsaktivitäten.\n4. Führe diese konsequent durch.\n5. Erfasse neue Interessenten.\n6. Prüfe monatlich, ob ausreichend neue Kontakte entstanden sind.",
            ],
            'A sufficient share of leads is converted into customers.' => [
                'module_key' => 'lead-quality',
                'knowledge_slug' => 'revenue',
                'recommendation' => "1. Erfasse alle Anfragen.\n2. Dokumentiere Angebote und Abschlüsse.\n3. Berechne Deine Abschlussquote.\n4. Analysiere verlorene Verkaufschancen.\n5. Verbessere Angebot, Kommunikation und Nachverfolgung.\n6. Überprüfe die Quote regelmäßig.",
            ],
            'Services/deliveries are provided as promised.' => [
                'module_key' => 'plan-hive',
                'knowledge_slug' => 'operations',
                'recommendation' => "1. Dokumentiere die vereinbarten Leistungen.\n2. Verwende klare Prozesse für die Umsetzung.\n3. Kontrolliere Termine und Qualität.\n4. Informiere Kunden frühzeitig über Abweichungen.\n5. Hole Feedback ein.\n6. Beseitige wiederkehrende Fehler systematisch.",
            ],
            'Customers meet payment and cooperation obligations.' => [
                'module_key' => 'invoice-maker',
                'knowledge_slug' => 'revenue',
                'recommendation' => "1. Stelle Rechnungen zeitnah.\n2. Verwende eindeutige Zahlungsziele.\n3. Überwache offene Forderungen.\n4. Erinnere Kunden freundlich an fällige Zahlungen.\n5. Nutze ein strukturiertes Mahnverfahren.\n6. Prüfe regelmäßig die Zahlungsmoral Deiner Kunden.",
            ],
            'Existing liabilities are systematically reduced; no risky new debt.' => [
                'module_key' => 'cash-core',
                'knowledge_slug' => 'profitability',
            ],
            'Contribution margins are healthy and actively improved.' => [
                'module_key' => 'financial-platform',
                'knowledge_slug' => 'profitability',
            ],
            'Customers make repeat purchases regularly.' => [
                'module_key' => 'sweet-spot',
                'knowledge_slug' => 'profitability',
            ],
            'Investments are made selectively for predictable returns.' => [
                'module_key' => 'cash-core',
                'knowledge_slug' => 'profitability',
            ],
            'Liquidity reserves cover several months of costs.' => [
                'module_key' => 'cash-core',
                'knowledge_slug' => 'profitability',
            ],
            'Bottlenecks and waste are continuously identified and reduced.' => [
                'module_key' => 'loop-engine',
                'knowledge_slug' => 'operations',
            ],
            'Tasks are assigned according to strengths and competencies.' => [
                'module_key' => 'org-matrix',
                'knowledge_slug' => 'operations',
            ],
            'The directly affected people can solve problems independently.' => [
                'module_key' => 'focus-matrix',
                'knowledge_slug' => 'operations',
            ],
            'Processes function even when key individuals are absent.' => [
                'module_key' => 'sop-builder',
                'knowledge_slug' => 'operations',
            ],
            'The company consistently delivers high quality and builds reputation.' => [
                'module_key' => 'plan-hive',
                'knowledge_slug' => 'operations',
            ],
            'Customers achieve noticeable improvements beyond the transaction.' => [
                'module_key' => 'sweet-spot',
                'knowledge_slug' => 'market-influence',
            ],
            'Employees are motivated by purpose and mission.' => [
                'module_key' => 'vision-flow',
                'knowledge_slug' => 'market-influence',
            ],
            "Employees' personal goals align with the company vision." => [
                'module_key' => 'nur-du',
                'knowledge_slug' => 'market-influence',
            ],
            'Critical and positive feedback is actively sought and used.' => [
                'module_key' => 'smart-kpi',
                'knowledge_slug' => 'market-influence',
            ],
            'Cooperations (including with competitors) improve the customer experience.' => [
                'module_key' => 'org-matrix',
                'knowledge_slug' => 'market-influence',
            ],
            'Customers support the company long-term and recommend it.' => [
                'module_key' => 'sweet-spot',
                'knowledge_slug' => 'organizational-legacy',
            ],
            'Leadership transitions are planned and practiced.' => [
                'module_key' => 'org-matrix',
                'knowledge_slug' => 'organizational-legacy',
            ],
            'People engage out of conviction — internally and externally.' => [
                'module_key' => 'vision-flow',
                'knowledge_slug' => 'organizational-legacy',
            ],
            'Regular alignment with a long-term vision.' => [
                'module_key' => 'nur-du',
                'knowledge_slug' => 'organizational-legacy',
            ],
            'The organization continuously learns and improves systemically.' => [
                'module_key' => 'knowledge-manager',
                'knowledge_slug' => 'organizational-legacy',
            ],
        ];

        foreach ($mappings as $questionText => $data) {
            $update = [
                'recommended_module_key' => $data['module_key'],
                'knowledge_slug' => $data['knowledge_slug'],
            ];
            if (! empty($data['recommendation'])) {
                $update['failure_recommendation'] = $data['recommendation'];
            }

            DB::table('auditpro_questions')
                ->where('question', $questionText)
                ->update($update);
        }

        // Fuzzy update for German or lead questions
        DB::table('auditpro_questions')
            ->where(function ($q) {
                $q->where('question', 'like', '%share of leads%')
                  ->orWhere('question', 'like', '%leads%converted%')
                  ->orWhere('question', 'like', '%Interessenten%Kunden%')
                  ->orWhere('question', 'like', '%Interessenten%zu Kunden%')
                  ->orWhere('question', 'like', '%Anteil an Leads%');
            })
            ->update([
                'recommended_module_key' => 'lead-quality',
                'knowledge_slug' => 'revenue',
            ]);

        // Fix revenue planner question #1
        DB::table('auditpro_questions')
            ->where(function ($q) {
                $q->where('question', 'like', '%monthly revenue is defined%')
                  ->orWhere('question', 'like', '%Mindestumsatz%')
                  ->orWhere('question', 'like', '%monatliche%Umsatz%definiert%');
            })
            ->update([
                'recommended_module_key' => 'revenue-planner',
                'knowledge_slug' => 'fixkosten',
            ]);
    }

    public function down(): void
    {
    }
};
