<?php

namespace Modules\AuditPro\Services;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
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

        return DB::transaction(function () use ($team): AuditTemplate {
            $template = AuditTemplate::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'name' => 'Business Maturity Assessment',
                'slug' => 'business-maturity',
                'description' => 'A five-pillar assessment covering revenue, profit, operations, influence, and legacy.',
                'is_default' => true,
            ]);

            foreach ($this->blueprint() as $pillarPosition => $pillarData) {
                $pillar = $template->pillars()->withoutGlobalScopes()->create([
                    'team_id' => $team->id,
                    'name' => $pillarData['name'],
                    'description' => $pillarData['description'],
                    'icon' => $pillarData['icon'],
                    'target_score' => 5,
                    'position' => $pillarPosition + 1,
                ]);

                foreach ($pillarData['questions'] as $questionPosition => $questionData) {
                    $pillar->questions()->withoutGlobalScopes()->create([
                        'team_id' => $team->id,
                        'template_id' => $template->id,
                        'question' => $questionData['question'],
                        'description' => $questionData['description'],
                        'failure_recommendation' => $questionData['recommendation'],
                        'position' => $questionPosition + 1,
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
                'description' => 'Market demand, delivery reliability, pricing, and customer growth.',
                'icon' => 'trending_up',
                'questions' => [
                    [
                        'question' => 'Market demand for the offer is stable and growing.',
                        'description' => 'Does the business have measurable, repeatable demand rather than relying on isolated opportunities?',
                        'recommendation' => 'Track qualified demand monthly and strengthen the channels producing consistent opportunities.',
                    ],
                    [
                        'question' => 'The value proposition is clear and differentiated.',
                        'description' => 'Can ideal customers quickly understand why this offer is preferable to alternatives?',
                        'recommendation' => 'Refine the positioning around a specific audience, outcome, and differentiating mechanism.',
                    ],
                    [
                        'question' => 'Pricing reflects the value delivered.',
                        'description' => 'Are prices supported by measurable customer outcomes and healthy market positioning?',
                        'recommendation' => 'Review pricing against delivered value, margin targets, and willingness-to-pay evidence.',
                    ],
                    [
                        'question' => 'Products and services are delivered reliably as promised.',
                        'description' => 'Does the delivery process consistently meet scope, quality, and timing commitments?',
                        'recommendation' => 'Document delivery workflows and introduce quality checkpoints for every client engagement.',
                    ],
                    [
                        'question' => 'Customers meet payment and cooperation obligations.',
                        'description' => 'Do customers pay on time and provide the inputs required for smooth delivery?',
                        'recommendation' => 'Use clear payment terms, automated reminders, and structured client onboarding.',
                    ],
                ],
            ],
            [
                'name' => 'Profit',
                'description' => 'Margins, liquidity, repeat purchases, debt, and investment discipline.',
                'icon' => 'payments',
                'questions' => [
                    [
                        'question' => 'Existing liabilities are systematically reduced without risky new debt.',
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
                        'question' => 'Investments are selected for predictable returns.',
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
                'name' => 'Operations',
                'description' => 'Process efficiency, role fit, autonomy, resilience, and quality.',
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
                        'question' => 'The people closest to a problem can solve it independently.',
                        'description' => 'Are team members empowered to make appropriate decisions without unnecessary escalation?',
                        'recommendation' => 'Define decision boundaries and train the team in structured problem-solving.',
                    ],
                    [
                        'question' => 'Processes continue when key individuals are absent.',
                        'description' => 'Are critical workflows documented and supported by trained backups?',
                        'recommendation' => 'Create a shared SOP library and cross-train a backup for every critical process.',
                    ],
                    [
                        'question' => 'The company consistently delivers high quality.',
                        'description' => 'Are quality standards embedded in delivery and measured after completion?',
                        'recommendation' => 'Define quality criteria, add review checklists, and track customer satisfaction.',
                    ],
                ],
            ],
            [
                'name' => 'Influence',
                'description' => 'Customer transformation, mission, alignment, feedback, and partnerships.',
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
                        'question' => 'Employees’ personal goals align with the company vision.',
                        'description' => 'Are development paths designed to serve both the person and the organization?',
                        'recommendation' => 'Use quarterly one-to-ones to align personal goals with company priorities.',
                    ],
                    [
                        'question' => 'Critical and positive feedback is actively sought and used.',
                        'description' => 'Does leadership collect honest feedback and visibly act on recurring themes?',
                        'recommendation' => 'Run customer and employee pulse surveys and review actions with leadership.',
                    ],
                    [
                        'question' => 'Partnerships improve the customer experience.',
                        'description' => 'Has the company built complementary relationships that create additional customer value?',
                        'recommendation' => 'Map the customer journey and develop partnerships around the largest experience gaps.',
                    ],
                ],
            ],
            [
                'name' => 'Legacy',
                'description' => 'Advocacy, succession, community, long-term vision, and learning.',
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
                        'question' => 'People engage out of genuine conviction.',
                        'description' => 'Does the mission attract voluntary support from employees, customers, and partners?',
                        'recommendation' => 'Communicate the organization’s purpose and create meaningful opt-in participation.',
                    ],
                    [
                        'question' => 'Activities are regularly aligned with a long-term vision.',
                        'description' => 'Does leadership use a consistent planning rhythm to protect strategic focus?',
                        'recommendation' => 'Adopt quarterly planning and review every initiative against the long-term vision.',
                    ],
                    [
                        'question' => 'The organization learns and improves systemically.',
                        'description' => 'Are retrospectives, feedback loops, and shared knowledge part of normal operations?',
                        'recommendation' => 'Run regular retrospectives, document lessons, and track one improvement priority each quarter.',
                    ],
                ],
            ],
        ];
    }
}
