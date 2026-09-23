<?php

namespace Modules\BookIntelligence\Services;

use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\CareerPath;
use Modules\BookIntelligence\Models\CompetencyRole;

class CompetencyFrameworkService
{
    /**
     * Seed or get default organizational competency roles.
     *
     * @return array<int, CompetencyRole>
     */
    public function ensureDefaultRoles(int $teamId): array
    {
        $existing = CompetencyRole::where('team_id', $teamId)->get();
        if ($existing->isNotEmpty()) {
            return $existing->all();
        }

        $rolesData = [
            [
                'name' => 'Sales Development Representative (SDR)',
                'slug' => 'sdr',
                'department' => 'Sales',
                'level' => 'starter',
                'description' => 'Executes cold outbound prospecting, qualifying leads, and booking discovery meetings.',
                'required_knowledge' => ['Cold Email Deliverability', 'ICP & Persona Mapping', 'CRM Hygiene', 'Objection Handling'],
                'required_competencies' => ['Outbound Prospecting', 'Active Listening', 'Pipeline Generation'],
                'required_skills' => ['Cold Calling', 'Sequencing Software', 'LinkedIn Outreach', 'Lead Qualification'],
            ],
            [
                'name' => 'Account Executive (AE)',
                'slug' => 'account-executive',
                'department' => 'Sales',
                'level' => 'professional',
                'description' => 'Conducts deep product demonstrations, runs MEDDPICC discovery, negotiates contracts, and closes new revenue.',
                'required_knowledge' => ['MEDDPICC Sales Methodology', 'Contract Negotiation', 'Value-Based Pricing', 'Competitive Positioning'],
                'required_competencies' => ['Deal Closing', 'Discovery & Pain Diagnosis', 'Executive Stakeholder Management'],
                'required_skills' => ['Contract Redlining', 'Multi-threading', 'Demo Customization', 'Objection Resolution'],
            ],
            [
                'name' => 'Head of Sales / VP Sales',
                'slug' => 'head-of-sales',
                'department' => 'Sales',
                'level' => 'expert',
                'description' => 'Builds, scales, and coaches high-performing revenue organizations and sets enterprise sales strategy.',
                'required_knowledge' => ['Sales Compensation Design', 'Quota Allocation', 'Forecasting & Pipeline Math', 'Sales Enablement Architecture'],
                'required_competencies' => ['Revenue Leadership', 'Sales Coaching', 'Executive Hiring', 'Capacity Planning'],
                'required_skills' => ['Board Reporting', 'Sales Rep Performance Management', 'Territory Design'],
            ],
            [
                'name' => 'Operations Manager / COO',
                'slug' => 'operations-manager',
                'department' => 'Operations',
                'level' => 'senior',
                'description' => 'Designs lean operational systems, manages unit economics, optimizes cash flow, and enforces execution discipline.',
                'required_knowledge' => ['Theory of Constraints', 'Lean Operations', 'Fixed vs Variable Costing', 'SOP Architecture'],
                'required_competencies' => ['Process Optimization', 'Cross-Functional Execution', 'Financial Health Management'],
                'required_skills' => ['Bottleneck Analysis', 'Cash Flow Forecasting', 'Automation Design'],
            ],
            [
                'name' => 'Revenue Operations (RevOps) Lead',
                'slug' => 'revops-lead',
                'department' => 'Revenue Operations',
                'level' => 'senior',
                'description' => 'Aligns Marketing, Sales, and Customer Success data, tooling, and compensation pipelines into a unified engine.',
                'required_knowledge' => ['Full-Funnel Analytics', 'CRM Data Architecture', 'Attribution Modeling', 'DSO & Billing Workflows'],
                'required_competencies' => ['Systems Architecture', 'Data-Driven Optimization', 'Toolchain Integration'],
                'required_skills' => ['SQL & Reporting', 'Pipeline Velocity Modeling', 'API Automations'],
            ],
            [
                'name' => 'Customer Success Manager (CSM)',
                'slug' => 'csm',
                'department' => 'Customer Success',
                'level' => 'professional',
                'description' => 'Drives customer onboarding, time-to-value, retention, net revenue expansion, and eliminates churn risks.',
                'required_knowledge' => ['Customer Journey Mapping', 'Health Scoring Metrics', 'Net Revenue Retention (NRR)', 'QBR Frameworks'],
                'required_competencies' => ['Customer Relationship Management', 'Churn Prevention', 'Account Expansion'],
                'required_skills' => ['QBR Execution', 'Adoption Tracking', 'Escalation Resolution'],
            ],
        ];

        $created = [];
        foreach ($rolesData as $r) {
            $created[] = CompetencyRole::create(array_merge($r, ['team_id' => $teamId]));
        }

        // Link parent career paths
        $sdr = collect($created)->firstWhere('slug', 'sdr');
        $ae = collect($created)->firstWhere('slug', 'account-executive');
        $headSales = collect($created)->firstWhere('slug', 'head-of-sales');

        if ($sdr && $ae) {
            $sdr->update(['next_role_id' => $ae->id]);
            CareerPath::create([
                'team_id' => $teamId,
                'from_role_id' => $sdr->id,
                'to_role_id' => $ae->id,
                'title' => 'SDR to Account Executive Trajectory',
                'description' => 'Transition from prospecting to closing complex enterprise deals.',
                'required_competencies' => ['MEDDPICC Discovery', 'Deal Closing', 'Contract Negotiation'],
                'recommended_book_ids' => Book::take(3)->pluck('id')->all(),
                'milestones' => [
                    'Hit quota for 3 consecutive quarters',
                    'Pass MEDDPICC Closing Assessment',
                    'Shadow 20 AE Discovery and Negotiation Calls',
                ],
            ]);
        }

        if ($ae && $headSales) {
            $ae->update(['next_role_id' => $headSales->id]);
            CareerPath::create([
                'team_id' => $teamId,
                'from_role_id' => $ae->id,
                'to_role_id' => $headSales->id,
                'title' => 'AE to Head of Sales Leadership Path',
                'description' => 'Transition from individual closing to building and scaling a sales organization.',
                'required_competencies' => ['Revenue Leadership', 'Sales Compensation Design', 'Pipeline Math & Forecasting'],
                'recommended_book_ids' => Book::take(3)->pluck('id')->all(),
                'milestones' => [
                    'Mentor 2 junior AEs to target attainment',
                    'Complete Sales Leadership Practical Challenge',
                    'Design team territory and compensation model',
                ],
            ]);
        }

        return $created;
    }
}
