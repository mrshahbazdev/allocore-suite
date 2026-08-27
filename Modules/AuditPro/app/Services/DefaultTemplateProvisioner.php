<?php

namespace Modules\AuditPro\Services;

use App\Models\Team;
use App\Services\QuestionToolGuesser;
use Illuminate\Support\Facades\DB;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;

class DefaultTemplateProvisioner
{
    public function provision(Team $team): AuditTemplate
    {
        $existing = AuditTemplate::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('slug', 'business-maturity')
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->synchronize($team);
    }

    public function synchronize(Team $team): AuditTemplate
    {
        return DB::transaction(function () use ($team): AuditTemplate {
            $template = AuditTemplate::withoutGlobalScopes()->updateOrCreate([
                'team_id' => $team->id,
                'slug' => 'business-maturity',
            ], [
                'name' => 'Business Maturity Assessment',
                'description' => 'The official 25-question Business Readiness framework covering revenue, profit, order, influence, and legacy.',
                'is_default' => true,
            ]);

            foreach ($this->blueprint() as $pillarPosition => $pillarData) {
                $pillar = AuditPillar::withoutGlobalScopes()->updateOrCreate([
                    'team_id' => $team->id,
                    'template_id' => $template->id,
                    'position' => $pillarPosition + 1,
                ], [
                    'name' => $pillarData['name'],
                    'description' => $pillarData['description'],
                    'icon' => $pillarData['icon'],
                    'target_score' => 4,
                ]);

                foreach ($pillarData['questions'] as $questionPosition => $questionData) {
                    AuditQuestion::withoutGlobalScopes()->updateOrCreate([
                        'team_id' => $team->id,
                        'template_id' => $template->id,
                        'pillar_id' => $pillar->id,
                        'position' => $questionPosition + 1,
                    ], [
                        'question' => $questionData['question'],
                        'description' => $questionData['description'],
                        'failure_recommendation' => $questionData['recommendation'],
                        'recommended_module_key' => $questionData['module_key'] ?? QuestionToolGuesser::guess($questionData['question'], $pillarData['name']),
                        'knowledge_slug' => $questionData['knowledge_slug'] ?? QuestionToolGuesser::guessKnowledgeSlug($questionData['question'], $pillarData['name']),
                        'question_type' => 'scale_1_to_5',
                        'weight' => 1,
                        'is_required' => true,
                    ]);
                }
            }

            return $template;
        });
    }

    private function blueprint(): array
    {
        return [
            [
                'name' => 'Revenue',
                'description' => 'The foundation of Business Readiness: reliable and predictable income generation.',
                'icon' => 'trending_up',
                'questions' => [
                    [
                        'question' => 'The required monthly revenue is defined and realistically planned.',
                        'description' => 'Viele Unternehmer kennen ihren bisherigen Umsatz, aber nicht den Umsatz, den ihr Unternehmen tatsächlich benötigt. Wenn Du Deinen erforderlichen Mindestumsatz kennst, kannst Du frühzeitig erkennen, ob Dein Unternehmen auf sicherem Kurs ist oder ob Handlungsbedarf besteht.',
                        'recommendation' => "1. Erfasse alle monatlichen Fixkosten.\n2. Ermittle die variablen Kosten Deiner Leistungen oder Produkte.\n3. Berücksichtige bekannte Kostensteigerungen.\n4. Berechne daraus den notwendigen Mindestumsatz.\n5. Plane diesen Umsatz auf Monate und Quartale.\n6. Vergleiche regelmäßig Planung und Realität.",
                        'module_key' => 'cash-core',
                        'knowledge_slug' => 'revenue',
                    ],
                    [
                        'question' => 'Suitable prospects are reached continuously.',
                        'description' => 'Neue Kunden fallen selten vom Himmel. Wenn Du kontinuierlich die richtigen Menschen auf Dein Angebot aufmerksam machst, schaffst Du die Grundlage für stabile Umsätze und mehr Planungssicherheit.',
                        'recommendation' => "1. Definiere Deine Zielkunden.\n2. Notiere, über welche Wege diese erreichbar sind.\n3. Plane regelmäßige Marketing- oder Vertriebsaktivitäten.\n4. Führe diese konsequent durch.\n5. Erfasse neue Interessenten.\n6. Prüfe monatlich, ob ausreichend neue Kontakte entstanden sind.",
                        'module_key' => 'keyword-cluster',
                        'knowledge_slug' => 'revenue',
                    ],
                    [
                        'question' => 'A sufficient share of leads is converted into customers.',
                        'description' => 'Interessenten allein sorgen noch nicht für Umsatz. Entscheidend ist, wie viele Anfragen am Ende tatsächlich zu Kunden werden. Schon kleine Verbesserungen können hier eine große Wirkung auf Deinen Umsatz haben.',
                        'recommendation' => "1. Erfasse alle Anfragen.\n2. Dokumentiere Angebote und Abschlüsse.\n3. Berechne Deine Abschlussquote.\n4. Analysiere verlorene Verkaufschancen.\n5. Verbessere Angebot, Kommunikation und Nachverfolgung.\n6. Überprüfe die Quote regelmäßig.",
                        'module_key' => 'lead-quality',
                        'knowledge_slug' => 'revenue',
                    ],
                    [
                        'question' => 'Services/deliveries are provided as promised.',
                        'description' => 'Kunden erwarten, dass Zusagen eingehalten werden. Wenn Du zuverlässig lieferst, stärkst Du das Vertrauen Deiner Kunden und erhöhst die Wahrscheinlichkeit für Folgeaufträge und Empfehlungen.',
                        'recommendation' => "1. Dokumentiere die vereinbarten Leistungen.\n2. Verwende klare Prozesse für die Umsetzung.\n3. Kontrolliere Termine und Qualität.\n4. Informiere Kunden frühzeitig über Abweichungen.\n5. Hole Feedback ein.\n6. Beseitige wiederkehrende Fehler systematisch.",
                        'module_key' => 'plan-hive',
                        'knowledge_slug' => 'operations',
                    ],
                    [
                        'question' => 'Customers meet payment and cooperation obligations.',
                        'description' => 'Umsatz allein hilft Dir wenig, wenn Rechnungen nicht bezahlt werden. Deshalb ist es wichtig, offene Forderungen im Blick zu behalten und Zahlungsausfälle möglichst früh zu vermeiden.',
                        'recommendation' => "1. Stelle Rechnungen zeitnah.\n2. Verwende eindeutige Zahlungsziele.\n3. Überwache offene Forderungen.\n4. Erinnere Kunden freundlich an fällige Zahlungen.\n5. Nutze ein strukturiertes Mahnverfahren.\n6. Prüfe regelmäßig die Zahlungsmoral Deiner Kunden.",
                        'module_key' => 'invoice-maker',
                        'knowledge_slug' => 'revenue',
                    ],
                ],
            ],
            [
                'name' => 'Profit',
                'description' => 'Whether revenue translates into healthy and sustainable profit margins.',
                'icon' => 'payments',
                'questions' => [
                    [
                        'question' => 'Existing liabilities are systematically reduced; no risky new debt.',
                        'description' => 'Is the company actively reducing obligations while protecting long-term stability?',
                        'recommendation' => 'Create a debt reduction plan and require an ROI review before taking new financing.',
                    ],
                    [
                        'question' => 'Contribution margins are healthy and actively improved.',
                        'description' => 'Do products and services cover direct costs, overhead, and a sustainable profit?',
                        'recommendation' => 'Calculate contribution margin by offer and reprice or redesign low-margin services.',
                    ],
                    [
                        'question' => 'Customers make repeat purchases regularly.',
                        'description' => 'Does repeat buying demonstrate satisfaction and durable customer value?',
                        'recommendation' => 'Track repeat purchase rate and introduce retention, follow-up, or subscription programs.',
                    ],
                    [
                        'question' => 'Investments are made selectively for predictable returns.',
                        'description' => 'Are significant investments based on clear return expectations rather than impulse?',
                        'recommendation' => 'Require a simple ROI forecast and payback threshold for material investments.',
                    ],
                    [
                        'question' => 'Liquidity reserves cover several months of costs.',
                        'description' => 'Can the company absorb a meaningful revenue disruption without immediate distress?',
                        'recommendation' => 'Build a reserve covering three to six months of operating expenses.',
                    ],
                ],
            ],
            [
                'name' => 'Order',
                'description' => 'The internal systems, processes, and team structures that allow the business to scale.',
                'icon' => 'account_tree',
                'questions' => [
                    [
                        'question' => 'Bottlenecks and waste are continuously identified and reduced.',
                        'description' => 'Does the team routinely improve workflows using measurable evidence?',
                        'recommendation' => 'Run a monthly process review and eliminate or automate one priority bottleneck.',
                    ],
                    [
                        'question' => 'Tasks are assigned according to strengths and competencies.',
                        'description' => 'Do people spend most of their time on work suited to their capabilities?',
                        'recommendation' => 'Review role fit quarterly and reassign recurring work to the strongest owner.',
                    ],
                    [
                        'question' => 'The directly affected people can solve problems independently.',
                        'description' => 'Are team members empowered to make appropriate decisions without unnecessary escalation?',
                        'recommendation' => 'Define decision boundaries and train the team in structured problem-solving.',
                    ],
                    [
                        'question' => 'Processes function even when key individuals are absent.',
                        'description' => 'Are critical workflows documented and supported by trained backups?',
                        'recommendation' => 'Create a shared SOP library and cross-train a backup for every critical process.',
                    ],
                    [
                        'question' => 'The company consistently delivers high quality and builds reputation.',
                        'description' => 'Are quality standards embedded in delivery and measured after completion?',
                        'recommendation' => 'Define quality criteria, add review checklists, and track customer satisfaction.',
                    ],
                ],
            ],
            [
                'name' => 'Influence',
                'description' => 'Brand authority, customer loyalty, meaningful impact, and market position.',
                'icon' => 'campaign',
                'questions' => [
                    [
                        'question' => 'Customers achieve noticeable improvements beyond the transaction.',
                        'description' => 'Does the company create measurable change for customers rather than only deliver inputs?',
                        'recommendation' => 'Define the customer transformation and collect before-and-after evidence.',
                    ],
                    [
                        'question' => 'Employees are motivated by purpose and mission.',
                        'description' => 'Do team members connect their work to a meaningful organizational purpose?',
                        'recommendation' => 'Clarify the mission and connect each role to its impact in onboarding and team rituals.',
                    ],
                    [
                        'question' => 'Employees\' personal goals align with the company vision.',
                        'description' => 'Are development paths designed to serve both the person and the organization?',
                        'recommendation' => 'Use quarterly one-to-ones to align personal goals with company priorities.',
                    ],
                    [
                        'question' => 'Critical and positive feedback is actively sought and used.',
                        'description' => 'Does leadership collect honest feedback and visibly act on recurring themes?',
                        'recommendation' => 'Run customer and employee pulse surveys and review actions with leadership.',
                    ],
                    [
                        'question' => 'Cooperations (including with competitors) improve the customer experience.',
                        'description' => 'Has the company built complementary relationships that create additional customer value?',
                        'recommendation' => 'Map the customer journey and develop partnerships around the largest experience gaps.',
                    ],
                ],
            ],
            [
                'name' => 'Legacy',
                'description' => 'Long-term sustainability, cultural health, succession, and societal impact.',
                'icon' => 'workspace_premium',
                'questions' => [
                    [
                        'question' => 'Customers support the company long-term and recommend it.',
                        'description' => 'Do loyal customers become active advocates and community members?',
                        'recommendation' => 'Launch a referral program and create experiences that strengthen customer belonging.',
                    ],
                    [
                        'question' => 'Leadership transitions are planned and practiced.',
                        'description' => 'Can the organization thrive beyond its current founders and key leaders?',
                        'recommendation' => 'Identify successors, create mentoring plans, and delegate increasing responsibility.',
                    ],
                    [
                        'question' => 'People engage out of conviction — internally and externally.',
                        'description' => 'Does the mission attract voluntary support from employees, customers, and partners?',
                        'recommendation' => 'Communicate the organization’s purpose and create meaningful opt-in participation.',
                    ],
                    [
                        'question' => 'Regular alignment with a long-term vision.',
                        'description' => 'Does leadership use a consistent planning rhythm to protect strategic focus?',
                        'recommendation' => 'Adopt quarterly planning and review every initiative against the long-term vision.',
                    ],
                    [
                        'question' => 'The organization continuously learns and improves systemically.',
                        'description' => 'Are retrospectives, feedback loops, and shared knowledge part of normal operations?',
                        'recommendation' => 'Run regular retrospectives, document lessons, and track one improvement priority each quarter.',
                    ],
                ],
            ],
        ];
    }
}
