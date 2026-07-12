<?php

namespace Modules\AuditPro\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditAnswer;
use Modules\AuditPro\Models\AuditResult;
use Modules\AuditPro\Support\Maturity;

#[Layout('layouts.shell')]
class Assessment extends Component
{
    use WithFileUploads;

    public Audit $audit;

    public int $currentStep = 1;

    public array $answers = [];

    public function mount(Audit $audit): void
    {
        abort_if($audit->status === 'completed', 404);

        $this->audit = $audit->load('template.pillars.questions');
        abort_unless($this->audit->template, 404);

        foreach ($this->audit->template->pillars as $pillar) {
            foreach ($pillar->questions as $question) {
                $this->answers[$question->id] = ['value' => null, 'comment' => ''];
            }
        }

        foreach ($this->audit->answers()->get() as $answer) {
            $this->answers[$answer->question_id] = [
                'value' => $answer->value['answer'] ?? null,
                'comment' => $answer->comment ?? '',
            ];
        }

        $this->currentStep = $this->firstIncompleteStep();
    }

    public function nextStep()
    {
        if (! $this->currentPillar()) {
            return null;
        }

        foreach ($this->currentQuestions() as $question) {
            $value = $this->answers[$question->id]['value'] ?? null;

            if ($question->is_required && $this->isEmpty($value)) {
                $this->addError("answers.{$question->id}.value", __('This question is required.'));
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return null;
        }

        $this->saveCurrentStep();

        if ($this->currentStep < $this->audit->template->pillars->count()) {
            $this->currentStep++;

            return null;
        }

        return $this->finish();
    }

    public function previousStep(): void
    {
        $this->saveCurrentStep();
        $this->currentStep = max(1, $this->currentStep - 1);
    }

    public function saveDraft()
    {
        $this->saveCurrentStep();

        return redirect()->route('audit.index')->with('success', __('Audit draft saved.'));
    }

    public function currentQuestions()
    {
        $pillar = $this->currentPillar();

        if (! $pillar) {
            return collect();
        }

        return $pillar->questions->filter(function ($question): bool {
            if (! $question->depends_on_question_id) {
                return true;
            }

            $parent = $this->answers[$question->depends_on_question_id]['value'] ?? null;

            return ! $this->isEmpty($parent)
                && ($question->depends_on_answer === null
                    || (string) $parent === (string) $question->depends_on_answer);
        });
    }

    private function currentPillar()
    {
        return $this->audit->template->pillars->get($this->currentStep - 1);
    }

    private function saveCurrentStep(): void
    {
        foreach ($this->currentQuestions() as $question) {
            $answer = $this->answers[$question->id] ?? ['value' => null, 'comment' => ''];
            $value = $answer['value'];

            if ($this->isEmpty($value)) {
                continue;
            }

            $evidencePath = null;
            if ($question->question_type === 'file_upload' && is_object($value)) {
                $evidencePath = $value->store('audit-evidence', 'public');
                $value = $evidencePath;
                $this->answers[$question->id]['value'] = $value;
            }

            AuditAnswer::updateOrCreate(
                ['audit_id' => $this->audit->id, 'question_id' => $question->id],
                [
                    'team_id' => $this->audit->team_id,
                    'value' => ['answer' => $value],
                    'comment' => $answer['comment'] ?: null,
                    'evidence_file_path' => $evidencePath,
                ],
            );
        }
    }

    private function finish()
    {
        $this->audit->load('template.pillars.questions', 'answers');
        $answers = $this->audit->answers->keyBy('question_id');

        foreach ($this->audit->template->pillars as $pillar) {
            $achieved = 0;
            $possible = 0;

            foreach ($pillar->questions as $question) {
                $answer = $answers->get($question->id);
                $score = $answer ? $this->score($question->question_type, $answer->value['answer'] ?? null, $question->options) : null;

                if ($score === null) {
                    continue;
                }

                $weight = (float) $question->weight;
                $achieved += $score * $weight;
                $possible += 5 * $weight;
            }

            $average = $possible > 0 ? ($achieved / $possible) * 5 : 0;

            AuditResult::updateOrCreate(
                ['audit_id' => $this->audit->id, 'level' => $pillar->name],
                [
                    'team_id' => $this->audit->team_id,
                    'pillar_id' => $pillar->id,
                    'average_score' => $average,
                    'maturity_level' => Maturity::label($average),
                    'total_points' => $achieved,
                ],
            );
        }

        $this->audit->update(['status' => 'completed']);

        return redirect()->route('audit.results', $this->audit);
    }

    private function score(string $type, mixed $value, ?array $options): ?float
    {
        return match ($type) {
            'scale_1_to_5' => is_numeric($value) ? min(5, max(1, (float) $value)) : null,
            'yes_no' => in_array($value, [true, 1, '1', 'yes'], true) ? 5 : 1,
            'radio', 'select' => is_numeric($value) ? min(5, max(0, (float) $value)) : null,
            'checkbox' => is_array($value) && count($options ?? []) > 0
                ? min(5, (count($value) / count($options)) * 5)
                : null,
            default => null,
        };
    }

    private function firstIncompleteStep(): int
    {
        foreach ($this->audit->template->pillars as $index => $pillar) {
            foreach ($pillar->questions as $question) {
                if ($question->is_required && $this->isEmpty($this->answers[$question->id]['value'] ?? null)) {
                    return $index + 1;
                }
            }
        }

        return max(1, $this->audit->template->pillars->count());
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || (is_array($value) && $value === []);
    }

    public function render()
    {
        return view('auditpro::livewire.assessment', [
            'pillar' => $this->currentPillar(),
            'questions' => $this->currentQuestions(),
            'stepCount' => $this->audit->template->pillars->count(),
        ]);
    }
}
