<?php

namespace Modules\AuditPro\Services;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;

class PillarTemplateProvisioner
{
    public function provision(Team $team, string $pillar): AuditTemplate
    {
        $slug = 'mini-'.strtolower($pillar);

        $existing = AuditTemplate::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('slug', $slug)
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->synchronize($team, $pillar);
    }

    public function synchronize(Team $team, string $pillar): AuditTemplate
    {
        return DB::transaction(function () use ($team, $pillar): AuditTemplate {
            $blueprint = $this->blueprintForPillar($pillar);

            $template = AuditTemplate::withoutGlobalScopes()->updateOrCreate([
                'team_id' => $team->id,
                'slug' => 'mini-'.strtolower($pillar),
            ], [
                'name' => $pillar.' Mini-Audit',
                'focus_pillar' => $pillar,
                'description' => 'A focused mini-audit for the '.$pillar.' pillar with deeper diagnostic questions.',
                'is_default' => false,
            ]);

            AuditPillar::withoutGlobalScopes()->where('template_id', $template->id)->delete();
            AuditQuestion::withoutGlobalScopes()->where('template_id', $template->id)->delete();

            $pillarModel = AuditPillar::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'template_id' => $template->id,
                'name' => $pillar,
                'description' => $blueprint['description'],
                'icon' => $blueprint['icon'],
                'target_score' => 4,
                'position' => 1,
            ]);

            foreach ($blueprint['questions'] as $position => $question) {
                AuditQuestion::withoutGlobalScopes()->create([
                    'team_id' => $team->id,
                    'template_id' => $template->id,
                    'pillar_id' => $pillarModel->id,
                    'position' => $position + 1,
                    'question' => $question['question'],
                    'description' => $question['description'] ?? '',
                    'failure_recommendation' => $question['recommendation'] ?? '',
                    'question_type' => 'scale_1_to_5',
                    'weight' => 1,
                    'is_required' => true,
                ]);
            }

            return $template;
        });
    }

    private function blueprintForPillar(string $pillar): array
    {
        return match ($pillar) {
            'Revenue' => $this->revenueBlueprint(),
            'Profit' => $this->profitBlueprint(),
            'Order' => $this->orderBlueprint(),
            'Influence' => $this->influenceBlueprint(),
            'Legacy' => $this->legacyBlueprint(),
            default => $this->revenueBlueprint(),
        };
    }

    private function revenueBlueprint(): array
    {
        return [
            'description' => 'Deep diagnostics for predictable, scalable income generation.',
            'icon' => 'trending_up',
            'questions' => [
                [
                    'question' => 'The required monthly revenue is defined and realistically planned.',
                    'description' => 'Is there a monthly revenue target based on fixed costs, margin goals, and market reality?',
                    'recommendation' => 'Define a monthly revenue target, break it into weekly goals, and review progress monthly.',
                ],
                [
                    'question' => 'Lead generation is continuous, measurable, and diversified.',
                    'description' => 'Does the business have multiple reliable channels that bring qualified prospects without depending on one source?',
                    'recommendation' => 'Build at least three repeatable lead sources: content, outbound, and referrals.',
                ],
                [
                    'question' => 'The conversion rate from lead to customer is known and improving.',
                    'description' => 'Is the sales funnel tracked and optimized at each stage?',
                    'recommendation' => 'Track conversion by stage and test one improvement per month in offer, follow-up, or close.',
                ],
                [
                    'question' => 'The sales process is documented and repeatable.',
                    'description' => 'Do new team members follow a defined sales process with scripts and milestones?',
                    'recommendation' => 'Document the sales process, create templates, and train the team on a common playbook.',
                ],
                [
                    'question' => 'Pricing covers costs, margin, and perceived value.',
                    'description' => 'Are prices set with a clear contribution margin target and regularly reviewed?',
                    'recommendation' => 'Calculate contribution margin per offer and adjust pricing at least twice per year.',
                ],
                [
                    'question' => 'Revenue visibility covers at least the next 90 days.',
                    'description' => 'Is there a pipeline or forecast that shows expected revenue for the next quarter?',
                    'recommendation' => 'Maintain a rolling 90-day revenue forecast and review it weekly.',
                ],
                [
                    'question' => 'Payment terms are enforced and cash-in is predictable.',
                    'description' => 'Do customers pay on time, and are reminders automated?',
                    'recommendation' => 'Use clear payment terms, deposits, and automated reminders to shorten cash cycles.',
                ],
                [
                    'question' => 'There is a systematic upsell, cross-sell, or retention program.',
                    'description' => 'Does the business actively grow customer lifetime value beyond the first sale?',
                    'recommendation' => 'Design one upsell or retention offer and track repeat purchase rate.',
                ],
            ],
        ];
    }

    private function profitBlueprint(): array
    {
        return [
            'description' => 'Deep diagnostics for healthy margins and cash stability.',
            'icon' => 'payments',
            'questions' => [
                [
                    'question' => 'Contribution margin is calculated per product or service.',
                    'description' => 'Do you know the true profit contribution of each offer after direct costs?',
                    'recommendation' => 'Calculate contribution margin per offer and redesign or reprice low-margin items.',
                ],
                [
                    'question' => 'A monthly profit and loss review is performed and understood.',
                    'description' => 'Is P&L reviewed monthly with actionable insights?',
                    'recommendation' => 'Schedule a monthly P&L review and define one financial action item.',
                ],
                [
                    'question' => 'The cost structure is reviewed quarterly for reduction opportunities.',
                    'description' => 'Are fixed and variable costs regularly challenged without harming quality?',
                    'recommendation' => 'Run a quarterly cost review and target one area for reduction or renegotiation.',
                ],
                [
                    'question' => 'Cashflow is forecast at least four weeks ahead.',
                    'description' => 'Do you know expected inflows and outflows for the next month?',
                    'recommendation' => 'Create a rolling 4- to 13-week cashflow forecast and review it weekly.',
                ],
                [
                    'question' => 'Debt is under control with a clear repayment plan.',
                    'description' => 'Are liabilities tracked and actively reduced?',
                    'recommendation' => 'List all liabilities, prioritize high-interest debt, and create a repayment schedule.',
                ],
                [
                    'question' => 'Profit-First allocation or a similar discipline is practiced.',
                    'description' => 'Is profit allocated before expenses, ensuring the business stays profitable?',
                    'recommendation' => 'Set up separate accounts for profit, taxes, and operating expenses.',
                ],
                [
                    'question' => 'Investments require a simple ROI estimate before approval.',
                    'description' => 'Are significant spends justified by expected return?',
                    'recommendation' => 'Require a one-page ROI estimate and payback period for investments above a threshold.',
                ],
                [
                    'question' => 'An emergency reserve covers at least three months of operating costs.',
                    'description' => 'Can the company survive a 90-day revenue disruption?',
                    'recommendation' => 'Build a reserve equal to 3-6 months of operating expenses.',
                ],
            ],
        ];
    }

    private function orderBlueprint(): array
    {
        return [
            'description' => 'Deep diagnostics for processes, roles, and scalable operations.',
            'icon' => 'account_tree',
            'questions' => [
                [
                    'question' => 'Core processes are mapped, documented, and accessible.',
                    'description' => 'Can team members find and follow documented workflows?',
                    'recommendation' => 'Map and document the top 5 core processes in a shared SOP library.',
                ],
                [
                    'question' => 'Roles and responsibilities are clearly defined.',
                    'description' => 'Does each team member know their responsibilities and decision scope?',
                    'recommendation' => 'Create a responsibility matrix with owners for every recurring task.',
                ],
                [
                    'question' => 'Decision authority is defined per role.',
                    'description' => 'Can appropriate decisions be made without unnecessary escalation?',
                    'recommendation' => 'Publish decision rights for spending, hiring, and client issues per role.',
                ],
                [
                    'question' => 'Quality standards and checklists are embedded in delivery.',
                    'description' => 'Is quality checked before work leaves the team?',
                    'recommendation' => 'Define quality criteria and add review checklists to each core process.',
                ],
                [
                    'question' => 'Cross-training reduces key-person risk.',
                    'description' => 'Can critical work continue if the primary owner is unavailable?',
                    'recommendation' => 'Identify critical roles and train a backup for each.',
                ],
                [
                    'question' => 'Process improvements are tracked monthly.',
                    'description' => 'Does the team regularly identify and remove bottlenecks?',
                    'recommendation' => 'Run a monthly process review and eliminate one bottleneck.',
                ],
                [
                    'question' => 'Tools support workflows without creating friction.',
                    'description' => 'Are project, time, and knowledge tools integrated into daily work?',
                    'recommendation' => 'Audit tools and consolidate or automate where duplicate work occurs.',
                ],
                [
                    'question' => 'Employee onboarding is standardized and repeatable.',
                    'description' => 'Do new hires receive a consistent introduction to culture, tools, and role?',
                    'recommendation' => 'Create an onboarding checklist and first-week schedule.',
                ],
            ],
        ];
    }

    private function influenceBlueprint(): array
    {
        return [
            'description' => 'Deep diagnostics for brand, reach, and customer loyalty.',
            'icon' => 'campaign',
            'questions' => [
                [
                    'question' => 'Brand positioning is clear and consistently communicated.',
                    'description' => 'Can the team and customers describe what makes the company different?',
                    'recommendation' => 'Define positioning, ideal customer, and key messages, then align all touchpoints.',
                ],
                [
                    'question' => 'Marketing channels are measured by return on investment.',
                    'description' => 'Do you know which channels produce leads and revenue profitably?',
                    'recommendation' => 'Track cost per lead and revenue per channel and reallocate budget to winners.',
                ],
                [
                    'question' => 'Customer feedback is actively collected and acted upon.',
                    'description' => 'Are reviews, surveys, and interviews part of a regular rhythm?',
                    'recommendation' => 'Run quarterly customer feedback cycles and publish action taken.',
                ],
                [
                    'question' => 'A referral or loyalty program exists and is promoted.',
                    'description' => 'Do satisfied customers have an easy way to recommend the business?',
                    'recommendation' => 'Launch a simple referral program and track referral revenue.',
                ],
                [
                    'question' => 'Thought leadership or content is produced regularly.',
                    'description' => 'Does the business publish insights that demonstrate expertise?',
                    'recommendation' => 'Create a content calendar with at least one valuable asset per month.',
                ],
                [
                    'question' => 'Customer success stories and case studies are collected.',
                    'description' => 'Are results and testimonials documented for sales and marketing?',
                    'recommendation' => 'Capture before-and-after metrics and publish case studies quarterly.',
                ],
                [
                    'question' => 'Strategic partnerships extend reach and value.',
                    'description' => 'Are there alliances that help customers or open new markets?',
                    'recommendation' => 'Map customer journey gaps and pursue one partnership per quarter.',
                ],
                [
                    'question' => 'A loyalty or satisfaction metric is tracked over time.',
                    'description' => 'Is there a repeatable score like NPS or CSAT?',
                    'recommendation' => 'Introduce NPS or CSAT and review trends monthly.',
                ],
            ],
        ];
    }

    private function legacyBlueprint(): array
    {
        return [
            'description' => 'Deep diagnostics for long-term vision, culture, and succession.',
            'icon' => 'workspace_premium',
            'questions' => [
                [
                    'question' => 'A long-term vision is written, shared, and used for decisions.',
                    'description' => 'Does leadership use a clear 5-10 year vision to filter opportunities?',
                    'recommendation' => 'Write a concise long-term vision and reference it in quarterly planning.',
                ],
                [
                    'question' => 'Succession plans exist for key roles.',
                    'description' => 'Could the business continue if a founder or key leader left?',
                    'recommendation' => 'Identify successors for critical roles and create development plans.',
                ],
                [
                    'question' => 'Company culture is defined and reinforced.',
                    'description' => 'Are values more than posters — are they used in hiring, reviews, and rituals?',
                    'recommendation' => 'Define values, integrate them into hiring and reviews, and celebrate examples.',
                ],
                [
                    'question' => 'A leadership development program is active.',
                    'description' => 'Are future leaders identified and grown intentionally?',
                    'recommendation' => 'Create a leadership development track with mentoring and stretch assignments.',
                ],
                [
                    'question' => 'A strategic planning rhythm is in place.',
                    'description' => 'Are quarterly and annual reviews used to protect long-term focus?',
                    'recommendation' => 'Adopt quarterly OKRs or rocks and an annual strategic review.',
                ],
                [
                    'question' => 'Knowledge is documented and shared across the team.',
                    'description' => 'Is institutional knowledge accessible beyond individual memory?',
                    'recommendation' => 'Build a knowledge base and require documentation for key processes.',
                ],
                [
                    'question' => 'Social and environmental impact is considered.',
                    'description' => 'Does the business consider its broader footprint and community?',
                    'recommendation' => 'Choose one impact initiative and track contribution quarterly.',
                ],
                [
                    'question' => 'An exit or sustainability plan exists.',
                    'description' => 'Is there a plan for transfer, sale, or long-term continuity?',
                    'recommendation' => 'Document an exit or continuity plan and review it annually.',
                ],
            ],
        ];
    }
}
