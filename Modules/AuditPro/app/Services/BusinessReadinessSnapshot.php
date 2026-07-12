<?php

namespace Modules\AuditPro\Services;

use App\Models\Team;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditAnswer;

class BusinessReadinessSnapshot
{
    public function __construct(
        private readonly DefaultTemplateProvisioner $provisioner,
    ) {}

    public function forTeam(?Team $team): array
    {
        if (! $team) {
            return [
                'latestAudit' => null,
                'overallScore' => null,
                'overallPercent' => null,
                'phases' => collect(),
                'activeAudits' => 0,
            ];
        }

        $template = $this->provisioner->provision($team)->load('pillars.questions');
        $latestAudit = Audit::withoutGlobalScopes()
            ->with(['results', 'answers'])
            ->where('team_id', $team->id)
            ->where('template_id', $template->id)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->first();
        $activeAudits = Audit::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('status', 'in_progress')
            ->count();
        $results = $latestAudit?->results->keyBy('pillar_id') ?? collect();
        $answers = $latestAudit?->answers->keyBy('question_id') ?? collect();
        $overallScore = $latestAudit ? (float) $latestAudit->results->avg('average_score') : null;

        $phases = $template->pillars->map(function ($pillar) use ($results, $answers): array {
            $score = $results->has($pillar->id)
                ? (float) $results->get($pillar->id)->average_score
                : null;
            $questionTarget = 20 / max(1, $pillar->questions->count());

            return [
                'name' => $pillar->name,
                'description' => $pillar->description,
                'target' => 20,
                'score' => $score,
                'contribution' => $this->contribution($score, 20),
                'questions' => $pillar->questions->map(function ($question) use ($answers, $questionTarget): array {
                    $score = $this->answerScore($answers->get($question->id));

                    return [
                        'position' => $question->position,
                        'name' => $question->question,
                        'description' => $question->description,
                        'target' => $questionTarget,
                        'score' => $score,
                        'contribution' => $this->contribution($score, $questionTarget),
                    ];
                }),
            ];
        });

        return [
            'latestAudit' => $latestAudit,
            'overallScore' => $overallScore,
            'overallPercent' => $this->contribution($overallScore, 100),
            'phases' => $phases,
            'activeAudits' => $activeAudits,
        ];
    }

    private function answerScore(?AuditAnswer $answer): ?float
    {
        $value = $answer?->value['answer'] ?? null;

        return is_numeric($value) ? max(0, min(5, (float) $value)) : null;
    }

    private function contribution(?float $score, float $target): ?float
    {
        return $score === null ? null : round(($score / 5) * $target, 1);
    }
}
