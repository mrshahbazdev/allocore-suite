<?php

namespace App\Services;

use App\Models\AllocoreScore;
use App\Models\GlossaryTerm;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditAnswer;
use Modules\AuditPro\Models\AuditQuestion;

class QuestionRecommendationService
{
    /**
     * Needs pyramid from the bottom up: Revenue first, then Profit, Order, Influence, Legacy.
     */
    protected array $pyramidOrder = ['Revenue', 'Profit', 'Order', 'Influence', 'Legacy'];

    public function __construct(
        private GlossaryService $glossaryService,
    ) {}

    public function gapsForScore(AllocoreScore $score, User $user): array
    {
        $audit = $score->audit?->loadMissing(['template.pillars.questions', 'answers']);

        if (! $audit) {
            return [];
        }

        $modules = Module::where('is_active', true)->get()->keyBy('key');
        $pillars = $this->orderedPillars($audit);

        $gaps = [];
        $position = 1;

        foreach ($pillars as $pillar) {
            foreach ($pillar->questions as $question) {
                $answer = $audit->answers->firstWhere('question_id', $question->id);
                $scoreValue = $this->questionScore($question, $answer);

                if ($scoreValue === null || $this->isFullyAnswered($scoreValue)) {
                    continue;
                }

                $moduleKey = $question->recommended_module_key ?: QuestionToolGuesser::guess($question->question, $pillar->name);
                $module = $modules->get($moduleKey);

                $gaps[] = $this->buildItem(
                    $score,
                    $user,
                    $pillar,
                    $question,
                    $answer,
                    $scoreValue,
                    $moduleKey,
                    $module,
                    $position++,
                );
            }
        }

        return $gaps;
    }

    /**
     * Return the largest remaining gap item for the dashboard focus card.
     */
    public function focusForScore(AllocoreScore $score, User $user): ?array
    {
        return $this->gapsForScore($score, $user)[0] ?? null;
    }

    protected function orderedPillars(Audit $audit): Collection
    {
        $pillars = $audit->template?->pillars;

        if (! $pillars) {
            return collect();
        }

        $order = array_flip($this->pyramidOrder);

        return $pillars->sortBy(fn ($pillar) => $order[$pillar->name] ?? 999);
    }

    protected function questionScore(AuditQuestion $question, ?AuditAnswer $answer): ?float
    {
        if (! $answer) {
            return 0;
        }

        $value = $answer->value['answer'] ?? null;

        if ($this->isEmptyValue($value, $question->question_type)) {
            return 0;
        }

        return $this->score($question->question_type, $value, $question->options);
    }

    protected function isEmptyValue(mixed $value, string $type): bool
    {
        return match ($type) {
            'checkbox' => ! is_array($value) || count($value) === 0,
            'file_upload' => blank($value),
            default => blank($value) && $value !== 0 && $value !== '0',
        };
    }

    protected function score(string $type, mixed $value, ?array $options): ?float
    {
        return match ($type) {
            'scale_1_to_5' => is_numeric($value) ? min(4, max(0, (float) $value)) : null,
            'yes_no' => in_array($value, [true, 1, '1', 'yes'], true) ? 4 : 0,
            'radio', 'select' => is_numeric($value) ? min(4, max(0, (float) $value)) : null,
            'checkbox' => is_array($value) && count($options ?? []) > 0
                ? min(4, (count($value) / count($options)) * 4)
                : null,
            default => null,
        };
    }

    protected function isFullyAnswered(float $scoreValue): bool
    {
        return $scoreValue >= 3.99;
    }

    protected function buildItem(
        AllocoreScore $score,
        User $user,
        $pillar,
        AuditQuestion $question,
        ?AuditAnswer $answer,
        float $questionScore,
        ?string $moduleKey,
        ?Module $module,
        int $priority,
    ): array {
        $manual = $question->failure_recommendation;

        if (blank($manual)) {
            $manual = __('Use the recommended tool to make measurable progress on :pillar.', ['pillar' => __($pillar->name)]);
        } else {
            $manual = __($manual);
        }

        $manual = $this->glossaryService->linkTerms($manual);

        $subscribed = $moduleKey && $user->hasModule($moduleKey);

        $knowledge = $this->knowledgeForQuestion($question, $moduleKey, $pillar->name);

        return [
            'priority' => $priority,
            'pillar' => __($pillar->name),
            'question_id' => $question->id,
            'question' => __($question->question),
            'description' => $question->description ? __($question->description) : null,
            'score' => round(($questionScore / 4) * 100, 2),
            'raw_score' => round($questionScore, 2),
            'max_score' => 4,
            'manual' => $manual,
            'module_key' => $moduleKey,
            'module_name' => $module ? __($module->name) : null,
            'module_description' => $module ? __($module->description) : null,
            'module_icon' => $module?->icon,
            'module_route' => $module?->route_prefix ? url('app/'.$module->route_prefix) : null,
            'subscribed' => $subscribed,
            'knowledge' => $knowledge,
            'benchmark' => $this->questionBenchmark($score, $pillar->name),
        ];
    }

    protected function knowledgeForQuestion(AuditQuestion $question, ?string $moduleKey, string $pillar): ?array
    {
        $term = null;

        if ($question->knowledge_slug) {
            $term = GlossaryTerm::published()->where('slug', $question->knowledge_slug)->first();
        }

        if (! $term && $moduleKey) {
            $term = $this->glossaryService->relatedForModule($moduleKey, 1)->first();
        }

        if (! $term) {
            $term = $this->glossaryService->relatedForPillar($pillar, 1)->first();
        }

        if (! $term) {
            $term = GlossaryTerm::published()->where('slug', 'allocore-score')->first();
        }

        if (! $term) {
            return null;
        }

        return [
            'term' => $term->term,
            'slug' => $term->slug,
            'definition' => Str::limit(strip_tags($term->simple_definition ?: $term->definition), 180),
            'link' => route('knowledge.show', $term->slug),
            'is_beginner_friendly' => $term->is_beginner_friendly,
        ];
    }

    protected function questionBenchmark(AllocoreScore $score, string $pillar): ?array
    {
        if (! $score->industry || ! $pillar) {
            return null;
        }

        $stats = AllocoreBenchmarkService::pillarStats($score->industry, $score->industry_sub, $pillar);

        if (($stats['count'] ?? 0) === 0) {
            return null;
        }

        $userScore = (float) (collect($score->pillars ?? [])->firstWhere('name', $pillar)['score'] ?? 0);
        $average = (float) ($stats['average'] ?? 0);
        $diff = round($userScore - $average, 1);

        return [
            'average' => $average,
            'count' => $stats['count'] ?? 0,
            'diff' => $diff,
            'better' => $diff > 0.01,
            'worse' => $diff < -0.01,
        ];
    }
}
