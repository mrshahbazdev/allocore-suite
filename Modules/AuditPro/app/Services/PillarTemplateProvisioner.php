<?php

namespace Modules\AuditPro\Services;

use App\Models\Team;
use App\Services\QuestionToolGuesser;
use Illuminate\Support\Facades\DB;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;
use Modules\AuditPro\Models\PillarQuestionBlueprint;

class PillarTemplateProvisioner
{
    public function provision(Team $team, string $pillar): AuditTemplate
    {
        $this->ensureDefaults($pillar);

        $existing = AuditTemplate::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('slug', 'mini-'.strtolower($pillar))
            ->first();

        if ($existing && $this->isFresh($existing, $pillar)) {
            return $existing;
        }

        return $this->synchronize($team, $pillar);
    }

    public function synchronize(Team $team, string $pillar): AuditTemplate
    {
        $this->ensureDefaults($pillar);

        return DB::transaction(function () use ($team, $pillar): AuditTemplate {
            $blueprint = $this->blueprintForPillar($pillar);

            $template = AuditTemplate::withoutGlobalScopes()->updateOrCreate([
                'team_id' => $team->id,
                'slug' => 'mini-'.strtolower($pillar),
            ], [
                'name' => $pillar.' Mini-Audit',
                'focus_pillar' => $pillar,
                'description' => 'A focused mini-audit for the '.$pillar.' pillar with deeper diagnostic question groups.',
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

            foreach ($blueprint['groups'] as $groupIndex => $group) {
                $mainPosition = ($groupIndex + 1) * 10;

                $mainQuestion = AuditQuestion::withoutGlobalScopes()->create([
                    'team_id' => $team->id,
                    'template_id' => $template->id,
                    'pillar_id' => $pillarModel->id,
                    'parent_id' => null,
                    'question' => $group['question'],
                    'description' => $group['description'] ?? '',
                    'failure_recommendation' => $group['recommendation'] ?? '',
                    'recommended_module_key' => QuestionToolGuesser::guess($group['question'], $pillar),
                    'knowledge_slug' => QuestionToolGuesser::guessKnowledgeSlug($group['question'], $pillar),
                    'question_type' => 'scale_1_to_5',
                    'weight' => 1,
                    'is_required' => true,
                    'position' => $mainPosition,
                ]);

                foreach ($group['follow_ups'] as $followUpIndex => $followUp) {
                    AuditQuestion::withoutGlobalScopes()->create([
                        'team_id' => $team->id,
                        'template_id' => $template->id,
                        'pillar_id' => $pillarModel->id,
                        'parent_id' => $mainQuestion->id,
                        'question' => $followUp['question'],
                        'description' => $followUp['description'] ?? '',
                        'failure_recommendation' => $followUp['recommendation'] ?? '',
                        'recommended_module_key' => QuestionToolGuesser::guess($followUp['question'], $pillar),
                        'knowledge_slug' => QuestionToolGuesser::guessKnowledgeSlug($followUp['question'], $pillar),
                        'question_type' => 'scale_1_to_5',
                        'weight' => 1,
                        'is_required' => true,
                        'position' => $mainPosition + $followUpIndex + 1,
                    ]);
                }
            }

            return $template;
        });
    }

    public function seedDefaults(string $pillar): void
    {
        $this->ensureDefaults($pillar);
    }

    private function isFresh(AuditTemplate $template, string $pillar): bool
    {
        $latestBlueprintAt = PillarQuestionBlueprint::where('pillar', $pillar)->max('updated_at');

        if ($latestBlueprintAt && $template->updated_at->lessThan($latestBlueprintAt)) {
            $hasInProgress = Audit::withoutGlobalScopes()
                ->where('template_id', $template->id)
                ->where('status', 'in_progress')
                ->exists();

            return $hasInProgress;
        }

        return true;
    }

    private function blueprintForPillar(string $pillar): array
    {
        $defaults = match ($pillar) {
            'Revenue' => $this->revenueBlueprint(),
            'Profit' => $this->profitBlueprint(),
            'Order' => $this->orderBlueprint(),
            'Influence' => $this->influenceBlueprint(),
            'Legacy' => $this->legacyBlueprint(),
            default => $this->revenueBlueprint(),
        };

        $groupsFromDb = PillarQuestionBlueprint::forPillar($pillar)
            ->mains()
            ->orderBy('position')
            ->get()
            ->map(function (PillarQuestionBlueprint $main) {
                return [
                    'question' => $main->question,
                    'description' => $main->description,
                    'recommendation' => $main->recommendation,
                    'follow_ups' => $main->children()->get()->map(fn ($q) => [
                        'question' => $q->question,
                        'description' => $q->description,
                        'recommendation' => $q->recommendation,
                    ])->all(),
                ];
            })
            ->all();

        if (! empty($groupsFromDb)) {
            $defaults['groups'] = $groupsFromDb;
        }

        return $defaults;
    }

    private function ensureDefaults(string $pillar): void
    {
        if (PillarQuestionBlueprint::where('pillar', $pillar)->exists()) {
            return;
        }

        $defaults = match ($pillar) {
            'Revenue' => $this->revenueBlueprint(),
            'Profit' => $this->profitBlueprint(),
            'Order' => $this->orderBlueprint(),
            'Influence' => $this->influenceBlueprint(),
            'Legacy' => $this->legacyBlueprint(),
            default => $this->revenueBlueprint(),
        };

        foreach ($defaults['groups'] as $groupIndex => $group) {
            $main = PillarQuestionBlueprint::create([
                'pillar' => $pillar,
                'parent_id' => null,
                'position' => ($groupIndex + 1) * 10,
                'question' => $group['question'],
                'description' => $group['description'] ?? null,
                'recommendation' => $group['recommendation'] ?? null,
                'is_active' => true,
            ]);

            foreach ($group['follow_ups'] as $followUpIndex => $followUp) {
                PillarQuestionBlueprint::create([
                    'pillar' => $pillar,
                    'parent_id' => $main->id,
                    'position' => ($groupIndex + 1) * 10 + $followUpIndex + 1,
                    'question' => $followUp['question'],
                    'description' => $followUp['description'] ?? null,
                    'recommendation' => $followUp['recommendation'] ?? null,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function revenueBlueprint(): array
    {
        return [
            'description' => 'Deep diagnostics for predictable, scalable income generation.',
            'icon' => 'trending_up',
            'groups' => [
                [
                    'question' => 'The required monthly revenue is defined and realistically planned.',
                    'description' => 'Does the business have a clearly defined monthly revenue target based on actual cost structure and market reality?',
                    'recommendation' => 'Define a specific monthly revenue goal based on fixed costs and the profit margin target. Break it down into weekly targets and review it monthly.',
                    'follow_ups' => [
                        ['question' => 'A weekly revenue target is derived from the monthly goal and tracked.', 'description' => 'Is weekly revenue broken down and monitored against the plan?'],
                        ['question' => 'Actual revenue is compared against target at least weekly.', 'description' => 'Do you review actual vs. planned revenue every week?'],
                        ['question' => 'Revenue progress is visible in a dashboard or tool.', 'description' => 'Is there a dashboard, spreadsheet, or tool where the team can see revenue progress?'],
                        ['question' => 'A specific role or person owns revenue growth.', 'description' => 'Is responsibility for hitting revenue targets assigned to a clear owner?'],
                    ],
                ],
                [
                    'question' => 'Suitable prospects are reached continuously.',
                    'description' => 'Does the company have a reliable system for consistently attracting qualified prospects rather than relying on occasional campaigns?',
                    'recommendation' => 'Build a consistent lead generation engine through channels such as content, outbound outreach, and referrals.',
                    'follow_ups' => [
                        ['question' => 'An ideal customer profile is written and used.', 'description' => 'Is the target customer clearly defined so marketing and sales can focus?'],
                        ['question' => 'Leads are generated through at least two consistent channels.', 'description' => 'Are there multiple reliable lead sources operating continuously?'],
                        ['question' => 'Cost per lead is tracked per channel.', 'description' => 'Do you know what each lead channel costs and how it performs?'],
                        ['question' => 'Lead quality is reviewed with sales at least monthly.', 'description' => 'Does marketing and sales meet to assess lead quality regularly?'],
                    ],
                ],
                [
                    'question' => 'A sufficient share of leads is converted into customers.',
                    'description' => 'Is the conversion rate from prospect to paying customer high enough to meet the planned monthly revenue goal?',
                    'recommendation' => 'Analyze funnel drop-off points and improve the offer, sales conversation, and follow-up process.',
                    'follow_ups' => [
                        ['question' => 'Conversion rate from lead to customer is measured.', 'description' => 'Do you track the percentage of leads that become customers?'],
                        ['question' => 'A documented sales process with stages exists.', 'description' => 'Can the sales team follow defined stages and actions?'],
                        ['question' => 'Follow-ups with prospects are automated or standardized.', 'description' => 'Are follow-up steps clear and consistently executed?'],
                        ['question' => 'The top reason leads do not convert is known.', 'description' => 'Do you analyze lost deals and address the main cause?'],
                    ],
                ],
                [
                    'question' => 'Services/deliveries are provided as promised.',
                    'description' => 'Does the delivery process consistently meet scope, quality, and timing commitments?',
                    'recommendation' => 'Document delivery workflows and introduce quality checkpoints for every client engagement.',
                    'follow_ups' => [
                        ['question' => 'Delivery scope, time, and quality are written in every agreement.', 'description' => 'Do client agreements clearly define what will be delivered and when?'],
                        ['question' => 'A standard handoff from sales to delivery exists.', 'description' => 'Is there a clear transition so delivery knows what was promised?'],
                        ['question' => 'Delivery deadlines are tracked in a project tool.', 'description' => 'Are tasks and deadlines visible and monitored in a tool?'],
                        ['question' => 'Delivery success is measured against client expectations.', 'description' => 'Do you check whether the client feels the promise was kept?'],
                    ],
                ],
                [
                    'question' => 'Customers meet payment and cooperation obligations.',
                    'description' => 'Do customers pay on time and provide the inputs required for smooth delivery?',
                    'recommendation' => 'Use clear payment terms, automated reminders, and structured client onboarding.',
                    'follow_ups' => [
                        ['question' => 'Payment terms are clearly stated in contracts and invoices.', 'description' => 'Do clients know exactly when payment is due?'],
                        ['question' => 'Invoice payment reminders are automated.', 'description' => 'Are late payments proactively followed up without manual effort?'],
                        ['question' => 'Days sales outstanding (DSO) is tracked monthly.', 'description' => 'Do you know how long it takes on average to get paid?'],
                        ['question' => 'A follow-up process for overdue payments exists.', 'description' => 'Is there a clear escalation for unpaid invoices?'],
                    ],
                ],
            ],
        ];
    }

    private function profitBlueprint(): array
    {
        return [
            'description' => 'Deep diagnostics for healthy margins and cash stability.',
            'icon' => 'payments',
            'groups' => [
                [
                    'question' => 'Existing liabilities are systematically reduced; no risky new debt.',
                    'description' => 'Is the company actively reducing obligations while protecting long-term stability?',
                    'recommendation' => 'Create a debt reduction plan and require an ROI review before taking new financing.',
                    'follow_ups' => [
                        ['question' => 'A written plan to reduce existing liabilities exists.', 'description' => 'Is there a timeline and amount to pay down debt?'],
                        ['question' => 'New financing decisions are reviewed against ROI.', 'description' => 'Is expected return checked before taking on new obligations?'],
                        ['question' => 'Debt-to-equity ratio is tracked.', 'description' => 'Do you know how leveraged the company is?'],
                        ['question' => 'Debt repayment is reviewed in monthly finance meetings.', 'description' => 'Is debt progress part of regular financial reviews?'],
                    ],
                ],
                [
                    'question' => 'Contribution margins are healthy and actively improved.',
                    'description' => 'Do products and services cover direct costs, overhead, and a sustainable profit?',
                    'recommendation' => 'Calculate contribution margin by offer and reprice or redesign low-margin services.',
                    'follow_ups' => [
                        ['question' => 'Contribution margin is calculated per product or service.', 'description' => 'Do you know the margin for each offer after direct costs?'],
                        ['question' => 'Low-margin offers are repriced or redesigned.', 'description' => 'Are unprofitable offers addressed rather than ignored?'],
                        ['question' => 'Gross margin is tracked monthly.', 'description' => 'Is there a monthly view of overall profitability?'],
                        ['question' => 'The break-even point is known.', 'description' => 'Do you know the revenue needed to cover all costs?'],
                    ],
                ],
                [
                    'question' => 'Customers make repeat purchases regularly.',
                    'description' => 'Does repeat buying demonstrate satisfaction and durable customer value?',
                    'recommendation' => 'Track repeat purchase rate and introduce retention, follow-up, or subscription programs.',
                    'follow_ups' => [
                        ['question' => 'Repeat purchase rate is measured.', 'description' => 'Do you know how often customers buy again?'],
                        ['question' => 'A retention or subscription offer exists.', 'description' => 'Is there a program that encourages ongoing purchases?'],
                        ['question' => 'Customers are segmented by purchase frequency.', 'description' => 'Do you distinguish one-time buyers from loyal customers?'],
                        ['question' => 'A post-purchase follow-up process exists.', 'description' => 'Do you stay in touch after the first sale to drive repeat business?'],
                    ],
                ],
                [
                    'question' => 'Investments are made selectively for predictable returns.',
                    'description' => 'Are significant investments based on clear return expectations rather than impulse?',
                    'recommendation' => 'Require a simple ROI forecast and payback threshold for material investments.',
                    'follow_ups' => [
                        ['question' => 'A minimum ROI threshold is required for investments.', 'description' => 'Is there a hurdle rate before spending is approved?'],
                        ['question' => 'Investment decisions are documented before spending.', 'description' => 'Is the expected return written down and approved?'],
                        ['question' => 'Actual returns are compared to projections.', 'description' => 'Do you review whether investments delivered the expected value?'],
                        ['question' => 'Investment approval is required above a set amount.', 'description' => 'Is there a clear authority limit for spending?'],
                    ],
                ],
                [
                    'question' => 'Liquidity reserves cover several months of costs.',
                    'description' => 'Can the company absorb a meaningful revenue disruption without immediate distress?',
                    'recommendation' => 'Build a reserve covering three to six months of operating expenses.',
                    'follow_ups' => [
                        ['question' => 'A cash reserve target is written and tracked.', 'description' => 'Do you know how much cash the company aims to hold?'],
                        ['question' => 'A 13-week cashflow forecast is maintained.', 'description' => 'Do you look at expected cash in and out for the next quarter?'],
                        ['question' => 'Unexpected expenses are covered by reserves.', 'description' => 'Can the business handle surprise costs without borrowing?'],
                        ['question' => 'The reserve amount is reviewed monthly.', 'description' => 'Is cash reserve progress part of regular finance reviews?'],
                    ],
                ],
            ],
        ];
    }

    private function orderBlueprint(): array
    {
        return [
            'description' => 'Deep diagnostics for processes, roles, and scalable operations.',
            'icon' => 'account_tree',
            'groups' => [
                [
                    'question' => 'Bottlenecks and waste are continuously identified and reduced.',
                    'description' => 'Does the team routinely improve workflows using measurable evidence?',
                    'recommendation' => 'Run a monthly process review and eliminate or automate one priority bottleneck.',
                    'follow_ups' => [
                        ['question' => 'Core processes are mapped and visualized.', 'description' => 'Can the team see how work flows end to end?'],
                        ['question' => 'A monthly process review meeting is held.', 'description' => 'Do you regularly review and improve workflows?'],
                        ['question' => 'Frontline staff report bottlenecks.', 'description' => 'Are the people doing the work able to flag problems?'],
                        ['question' => 'Improvements are tracked until resolved.', 'description' => 'Are process fixes assigned and followed through?'],
                    ],
                ],
                [
                    'question' => 'Tasks are assigned according to strengths and competencies.',
                    'description' => 'Do people spend most of their time on work suited to their capabilities?',
                    'recommendation' => 'Review role fit quarterly and reassign recurring work to the strongest owner.',
                    'follow_ups' => [
                        ['question' => 'Role profiles are updated with required competencies.', 'description' => 'Do role descriptions match the skills needed?'],
                        ['question' => 'Employees spend the majority of time in their strengths.', 'description' => 'Is work aligned with what each person does best?'],
                        ['question' => 'Workload is balanced across the team.', 'description' => 'Is work distributed fairly and realistically?'],
                        ['question' => 'A skills matrix is maintained.', 'description' => 'Do you know who has which skills and where gaps exist?'],
                    ],
                ],
                [
                    'question' => 'The directly affected people can solve problems independently.',
                    'description' => 'Are team members empowered to make appropriate decisions without unnecessary escalation?',
                    'recommendation' => 'Define decision boundaries and train the team in structured problem-solving.',
                    'follow_ups' => [
                        ['question' => 'Decision rights are documented per role.', 'description' => 'Does each person know what they can decide on their own?'],
                        ['question' => 'Employees have access to needed information.', 'description' => 'Can team members get the data required to make decisions?'],
                        ['question' => 'An escalation path exists for unclear cases.', 'description' => 'Do people know when and how to escalate?'],
                        ['question' => 'Problem-solving results are shared with the team.', 'description' => 'Are lessons from solved problems communicated to others?'],
                    ],
                ],
                [
                    'question' => 'Processes function even when key individuals are absent.',
                    'description' => 'Are critical workflows documented and supported by trained backups?',
                    'recommendation' => 'Create a shared SOP library and cross-train a backup for every critical process.',
                    'follow_ups' => [
                        ['question' => 'Critical processes are documented in a shared library.', 'description' => 'Can anyone find and follow key workflows?'],
                        ['question' => 'A backup or trained person exists for each critical role.', 'description' => 'Is there coverage if a key person is unavailable?'],
                        ['question' => 'SOPs are updated when processes change.', 'description' => 'Are documents kept current as workflows evolve?'],
                        ['question' => 'New employees are trained on SOPs.', 'description' => 'Is onboarding connected to documented processes?'],
                    ],
                ],
                [
                    'question' => 'The company consistently delivers high quality and builds reputation.',
                    'description' => 'Are quality standards embedded in delivery and measured after completion?',
                    'recommendation' => 'Define quality criteria, add review checklists, and track customer satisfaction.',
                    'follow_ups' => [
                        ['question' => 'Quality criteria are defined per service or product.', 'description' => 'Is it clear what "good" looks like for each deliverable?'],
                        ['question' => 'A checklist is used before delivery.', 'description' => 'Is quality checked before work reaches the customer?'],
                        ['question' => 'Customer satisfaction is measured after delivery.', 'description' => 'Do you ask clients whether expectations were met?'],
                        ['question' => 'Quality issues are reviewed and fixed systematically.', 'description' => 'Are problems tracked to root cause and prevented from repeating?'],
                    ],
                ],
            ],
        ];
    }

    private function influenceBlueprint(): array
    {
        return [
            'description' => 'Deep diagnostics for brand, reach, and customer loyalty.',
            'icon' => 'campaign',
            'groups' => [
                [
                    'question' => 'Customers achieve noticeable improvements beyond the transaction.',
                    'description' => 'Does the company create measurable change for customers rather than only deliver inputs?',
                    'recommendation' => 'Define the customer transformation and collect before-and-after evidence.',
                    'follow_ups' => [
                        ['question' => 'Customer success is defined with measurable outcomes.', 'description' => 'Do you know what success looks like for the customer?'],
                        ['question' => 'Before-and-after evidence is collected.', 'description' => 'Do you document the difference your product or service makes?'],
                        ['question' => 'Success stories are documented for marketing and sales.', 'description' => 'Are customer wins used to attract new clients?'],
                        ['question' => 'A customer onboarding path leads to first value.', 'description' => 'Do new customers reach their first success quickly?'],
                    ],
                ],
                [
                    'question' => 'Employees are motivated by purpose and mission.',
                    'description' => 'Do team members connect their work to a meaningful organizational purpose?',
                    'recommendation' => 'Clarify the mission and connect each role to its impact in onboarding and team rituals.',
                    'follow_ups' => [
                        ['question' => 'The company mission is communicated regularly.', 'description' => 'Do employees hear and remember the mission?'],
                        ['question' => 'Employees see how their work impacts the mission.', 'description' => 'Can each person connect daily tasks to the bigger purpose?'],
                        ['question' => 'Purpose is reinforced in team meetings.', 'description' => 'Is the mission mentioned in regular meetings?'],
                        ['question' => 'Leaders share stories of mission impact.', 'description' => 'Do leaders make the mission tangible with examples?'],
                    ],
                ],
                [
                    'question' => 'Employees\' personal goals align with the company vision.',
                    'description' => 'Are development paths designed to serve both the person and the organization?',
                    'recommendation' => 'Use quarterly one-to-ones to align personal goals with company priorities.',
                    'follow_ups' => [
                        ['question' => 'Personal development goals are discussed quarterly.', 'description' => 'Do managers and employees talk about growth regularly?'],
                        ['question' => 'A growth plan exists for each employee.', 'description' => 'Are development steps written and tracked?'],
                        ['question' => 'Company goals are translated to team objectives.', 'description' => 'Can teams see how their work supports the vision?'],
                        ['question' => 'Reviews connect personal growth to company vision.', 'description' => 'Do performance discussions link individual and company direction?'],
                    ],
                ],
                [
                    'question' => 'Critical and positive feedback is actively sought and used.',
                    'description' => 'Does leadership collect honest feedback and visibly act on recurring themes?',
                    'recommendation' => 'Run customer and employee pulse surveys and review actions with leadership.',
                    'follow_ups' => [
                        ['question' => 'Customer and employee feedback surveys are scheduled.', 'description' => 'Is feedback collected on a regular rhythm?'],
                        ['question' => 'Feedback results are reviewed with leadership.', 'description' => 'Do leaders look at the data and discuss it?'],
                        ['question' => 'Action is taken on the top three themes.', 'description' => 'Are the most common issues addressed?'],
                        ['question' => 'Feedback results are communicated back to participants.', 'description' => 'Do people know what changed because of their feedback?'],
                    ],
                ],
                [
                    'question' => 'Cooperations (including with competitors) improve the customer experience.',
                    'description' => 'Has the company built complementary relationships that create additional customer value?',
                    'recommendation' => 'Map the customer journey and develop partnerships around the largest experience gaps.',
                    'follow_ups' => [
                        ['question' => 'The customer journey is mapped.', 'description' => 'Do you know the steps customers take and where they struggle?'],
                        ['question' => 'Partnerships are selected to fill experience gaps.', 'description' => 'Are partners chosen to improve the customer journey?'],
                        ['question' => 'Partnership results are measured.', 'description' => 'Do you know whether partnerships create value?'],
                        ['question' => 'Partners receive feedback and renewals are reviewed.', 'description' => 'Are partner relationships maintained and evaluated?'],
                    ],
                ],
            ],
        ];
    }

    private function legacyBlueprint(): array
    {
        return [
            'description' => 'Deep diagnostics for long-term vision, culture, and succession.',
            'icon' => 'workspace_premium',
            'groups' => [
                [
                    'question' => 'Customers support the company long-term and recommend it.',
                    'description' => 'Do loyal customers become active advocates and community members?',
                    'recommendation' => 'Launch a referral program and create experiences that strengthen customer belonging.',
                    'follow_ups' => [
                        ['question' => 'Net Promoter Score or referral rate is tracked.', 'description' => 'Do you measure how likely customers are to recommend you?'],
                        ['question' => 'A referral program exists and is promoted.', 'description' => 'Do customers have an easy way and incentive to refer others?'],
                        ['question' => 'Loyal customers are recognized.', 'description' => 'Are long-term customers appreciated and rewarded?'],
                        ['question' => 'Customer lifetime value is tracked.', 'description' => 'Do you know the total value a customer brings over time?'],
                    ],
                ],
                [
                    'question' => 'Leadership transitions are planned and practiced.',
                    'description' => 'Can the organization thrive beyond its current founders and key leaders?',
                    'recommendation' => 'Identify successors, create mentoring plans, and delegate increasing responsibility.',
                    'follow_ups' => [
                        ['question' => 'A succession plan is documented for key roles.', 'description' => 'Is there a written plan for who takes over critical roles?'],
                        ['question' => 'Potential successors are identified and developed.', 'description' => 'Are future leaders being grown for each key role?'],
                        ['question' => 'Decision authority is delegated progressively.', 'description' => 'Are successors given more responsibility over time?'],
                        ['question' => 'Knowledge and relationships are transferred.', 'description' => 'Are key information and connections shared with successors?'],
                    ],
                ],
                [
                    'question' => 'People engage out of conviction — internally and externally.',
                    'description' => 'Does the mission attract voluntary support from employees, customers, and partners?',
                    'recommendation' => 'Communicate the organization’s purpose and create meaningful opt-in participation.',
                    'follow_ups' => [
                        ['question' => 'Company values are visible in daily work.', 'description' => 'Do values show up in decisions and behavior?'],
                        ['question' => 'Employees and customers voluntarily advocate.', 'description' => 'Do people recommend the company without being asked?'],
                        ['question' => 'Stories of purpose are shared externally.', 'description' => 'Does the company communicate its mission publicly?'],
                        ['question' => 'Culture is measured and improved.', 'description' => 'Do you track and act on cultural health?'],
                    ],
                ],
                [
                    'question' => 'Regular alignment with a long-term vision.',
                    'description' => 'Does leadership use a consistent planning rhythm to protect strategic focus?',
                    'recommendation' => 'Adopt quarterly planning and review every initiative against the long-term vision.',
                    'follow_ups' => [
                        ['question' => 'A long-term vision is written and communicated.', 'description' => 'Is the future direction documented and shared?'],
                        ['question' => 'Quarterly priorities are derived from the vision.', 'description' => 'Do short-term goals connect to long-term direction?'],
                        ['question' => 'Progress toward the vision is reviewed annually.', 'description' => 'Do you check how far the company has moved toward its vision?'],
                        ['question' => 'Initiatives are filtered against the vision.', 'description' => 'Are new projects checked for alignment before approval?'],
                    ],
                ],
                [
                    'question' => 'The organization continuously learns and improves systemically.',
                    'description' => 'Are retrospectives, feedback loops, and shared knowledge part of normal operations?',
                    'recommendation' => 'Run regular retrospectives, document lessons, and track one improvement priority each quarter.',
                    'follow_ups' => [
                        ['question' => 'Retrospectives are held after projects.', 'description' => 'Does the team review what went well and what to improve?'],
                        ['question' => 'A shared knowledge base exists.', 'description' => 'Is organizational knowledge stored where everyone can access it?'],
                        ['question' => 'Lessons learned are turned into SOPs.', 'description' => 'Are improvements from retrospectives added to documented processes?'],
                        ['question' => 'One improvement priority is tracked per quarter.', 'description' => 'Is there a focused improvement goal each quarter?'],
                    ],
                ],
            ],
        ];
    }
}
