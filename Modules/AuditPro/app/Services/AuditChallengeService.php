<?php

namespace Modules\AuditPro\Services;

use App\Models\Module;
use App\Models\User;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditChallenge;

class AuditChallengeService
{
    protected array $pillarMap = [
        'Revenue' => [
            'target' => 'Set a concrete monthly revenue target for the next 90 days.',
            'do' => 'Log at least 10 leads/deals and track their conversion in the recommended tool.',
            'check' => 'Compare actual revenue against the target in the dashboard.',
            'act' => 'Document one improvement and schedule the next KPI check.',
        ],
        'Profit' => [
            'target' => 'Define a minimum contribution margin for each main offer.',
            'do' => 'Calculate contribution margins and update pricing where needed.',
            'check' => 'Review profit and cashflow trends for the last 30 days.',
            'act' => 'Write down one cost reduction or pricing action for the next 30 days.',
        ],
        'Order' => [
            'target' => 'Choose one recurring process to standardize.',
            'do' => 'Create the SOP or project template in the recommended tool.',
            'check' => 'Run the process once and measure time/quality.',
            'act' => 'Identify one bottleneck and assign an owner to fix it.',
        ],
        'Influence' => [
            'target' => 'Set a lead quality or referral target for the next 30 days.',
            'do' => 'Publish or outreach using the recommended tool to grow reach.',
            'check' => 'Review leads, engagement, or referral numbers.',
            'act' => 'Decide one message or channel to double down on.',
        ],
        'Legacy' => [
            'target' => 'Define the next long-term milestone for the company.',
            'do' => 'Capture mission, values, or successor planning in the recommended tool.',
            'check' => 'Review alignment between team actions and long-term vision.',
            'act' => 'Schedule a leadership or team ritual to reinforce the vision.',
        ],
    ];

    public function createFromAudit(Audit $audit, User $user): AuditChallenge
    {
        $pillar = $audit->focus_pillar ?? $audit->results->first()?->level;

        if (! $pillar) {
            throw new \InvalidArgumentException('Audit has no pillar to base a challenge on.');
        }

        $steps = $this->buildSteps($pillar, $user);

        return AuditChallenge::create([
            'team_id' => $audit->team_id,
            'user_id' => $user->id,
            'small_audit_id' => $audit->id,
            'pillar' => $pillar,
            'status' => 'in_progress',
            'steps' => $steps,
            'progress' => 0,
            'started_at' => now(),
            'next_challenge_at' => now()->addWeeks(4),
        ]);
    }

    public function buildSteps(string $pillar, User $user): array
    {
        $actions = $this->pillarMap[$pillar] ?? $this->pillarMap['Revenue'];
        $module = $this->recommendedModule($pillar, $user);

        $stepKeys = ['plan', 'do', 'check', 'act'];
        $steps = [];

        foreach ($stepKeys as $index => $key) {
            $steps[] = [
                'id' => $key,
                'order' => $index + 1,
                'label' => $actions[$key],
                'completed' => false,
                'module_key' => $module?->key,
                'module_name' => $module?->name,
                'module_route' => $module?->route_prefix ? url('app/'.$module->route_prefix) : null,
                'subscribed' => $module ? $user->hasModule($module->key) : false,
            ];
        }

        return $steps;
    }

    public function recommendedModule(string $pillar, User $user): ?Module
    {
        $map = [
            'Revenue' => ['financial-platform', 'invoice-maker', 'sweet-spot'],
            'Profit' => ['cash-core', 'financial-platform', 'sweet-spot'],
            'Order' => ['plan-hive', 'time-butler', 'loop-engine', 'focus-matrix'],
            'Influence' => ['keyword-cluster', 'lead-quality', 'bunny-band'],
            'Legacy' => ['vision-flow', 'nur-du', 'org-matrix'],
        ];

        $modules = Module::where('is_active', true)->get()->keyBy('key');

        $targetKey = collect($map[$pillar] ?? [])->first(fn ($key) => $modules->has($key));

        return $targetKey ? $modules->get($targetKey) : null;
    }

    public function toggleStep(AuditChallenge $challenge, string $stepId, bool $completed): void
    {
        $steps = $challenge->steps;

        foreach ($steps as &$step) {
            if ($step['id'] === $stepId) {
                $step['completed'] = $completed;
            }
        }

        $challenge->steps = $steps;
        $challenge->progress = $challenge->completionPercentage();

        if ($challenge->progress >= 100) {
            $challenge->status = 'completed';
            $challenge->completed_at = now();
            $challenge->next_challenge_at = now()->addWeeks(4);
        } else {
            $challenge->status = 'in_progress';
            $challenge->completed_at = null;
        }

        $challenge->save();
    }

    public function canStartForTeam(int $teamId, string $pillar): bool
    {
        $active = AuditChallenge::where('team_id', $teamId)
            ->where('pillar', $pillar)
            ->whereIn('status', ['open', 'in_progress'])
            ->exists();

        if ($active) {
            return false;
        }

        $lastCompleted = AuditChallenge::where('team_id', $teamId)
            ->where('pillar', $pillar)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        if (! $lastCompleted || ! $lastCompleted->completed_at) {
            return true;
        }

        return $lastCompleted->completed_at->diffInDays(now()) >= 28;
    }

    public function latestForPillar(int $teamId, string $pillar): ?AuditChallenge
    {
        return AuditChallenge::where('team_id', $teamId)
            ->where('pillar', $pillar)
            ->latest()
            ->first();
    }
}
