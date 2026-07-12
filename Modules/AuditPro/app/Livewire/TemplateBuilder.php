<?php

namespace Modules\AuditPro\Livewire;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;

#[Layout('layouts.shell')]
class TemplateBuilder extends Component
{
    public AuditTemplate $template;

    public bool $showPillarModal = false;

    public ?int $pillarId = null;

    public string $pillarName = '';

    public string $pillarDescription = '';

    public string $pillarIcon = 'account_tree';

    public float $pillarTargetScore = 5;

    public bool $showQuestionModal = false;

    public ?int $questionId = null;

    public ?int $questionPillarId = null;

    public string $questionText = '';

    public string $questionDescription = '';

    public string $questionType = 'scale_1_to_5';

    public float $questionWeight = 1;

    public bool $questionIsRequired = true;

    public string $questionFailureRecommendation = '';

    public string $questionOptions = '';

    public ?int $questionDependsOnId = null;

    public string $questionDependsOnAnswer = '';

    public function mount(AuditTemplate $template): void
    {
        $this->template = $template;
    }

    public function createPillar(): void
    {
        $this->resetPillarForm();
        $this->showPillarModal = true;
    }

    public function editPillar(int $id): void
    {
        $pillar = AuditPillar::where('template_id', $this->template->id)->findOrFail($id);
        $this->pillarId = $pillar->id;
        $this->pillarName = $pillar->name;
        $this->pillarDescription = $pillar->description ?? '';
        $this->pillarIcon = $pillar->icon ?? 'account_tree';
        $this->pillarTargetScore = (float) $pillar->target_score;
        $this->showPillarModal = true;
    }

    public function savePillar(): void
    {
        $validated = $this->validate([
            'pillarName' => ['required', 'string', 'max:255'],
            'pillarDescription' => ['nullable', 'string'],
            'pillarIcon' => ['nullable', 'string', 'max:100'],
            'pillarTargetScore' => ['required', 'numeric', 'min:1', 'max:5'],
        ]);
        $data = [
            'name' => $validated['pillarName'],
            'description' => $validated['pillarDescription'],
            'icon' => $validated['pillarIcon'],
            'target_score' => $validated['pillarTargetScore'],
        ];

        if ($this->pillarId) {
            AuditPillar::where('template_id', $this->template->id)->findOrFail($this->pillarId)->update($data);
        } else {
            $this->template->pillars()->create($data + [
                'position' => ((int) $this->template->pillars()->max('position')) + 1,
            ]);
        }

        $this->showPillarModal = false;
        $this->resetPillarForm();
    }

    public function deletePillar(int $id): void
    {
        AuditPillar::where('template_id', $this->template->id)->findOrFail($id)->delete();
    }

    public function createQuestion(int $pillarId): void
    {
        AuditPillar::where('template_id', $this->template->id)->findOrFail($pillarId);
        $this->resetQuestionForm();
        $this->questionPillarId = $pillarId;
        $this->showQuestionModal = true;
    }

    public function editQuestion(int $id): void
    {
        $question = AuditQuestion::where('template_id', $this->template->id)->findOrFail($id);
        $this->questionId = $question->id;
        $this->questionPillarId = $question->pillar_id;
        $this->questionText = $question->question;
        $this->questionDescription = $question->description ?? '';
        $this->questionType = $question->question_type;
        $this->questionWeight = (float) $question->weight;
        $this->questionIsRequired = $question->is_required;
        $this->questionFailureRecommendation = $question->failure_recommendation ?? '';
        $this->questionOptions = implode(', ', $question->options ?? []);
        $this->questionDependsOnId = $question->depends_on_question_id;
        $this->questionDependsOnAnswer = $question->depends_on_answer ?? '';
        $this->showQuestionModal = true;
    }

    public function saveQuestion(): void
    {
        $teamId = auth()->user()->current_team_id;
        $validated = $this->validate([
            'questionPillarId' => [
                'required',
                Rule::exists('auditpro_pillars', 'id')
                    ->where('team_id', $teamId)
                    ->where('template_id', $this->template->id),
            ],
            'questionText' => ['required', 'string'],
            'questionDescription' => ['nullable', 'string'],
            'questionType' => ['required', Rule::in([
                'scale_1_to_5',
                'yes_no',
                'text_input',
                'select',
                'radio',
                'checkbox',
                'file_upload',
            ])],
            'questionWeight' => ['required', 'numeric', 'min:0.1', 'max:10'],
            'questionIsRequired' => ['boolean'],
            'questionFailureRecommendation' => ['nullable', 'string'],
            'questionOptions' => ['nullable', 'string'],
            'questionDependsOnId' => [
                'nullable',
                Rule::exists('auditpro_questions', 'id')
                    ->where('team_id', $teamId)
                    ->where('template_id', $this->template->id),
            ],
            'questionDependsOnAnswer' => ['nullable', 'string'],
        ]);

        $options = collect(explode(',', $validated['questionOptions']))
            ->map(fn (string $option) => trim($option))
            ->filter()
            ->values()
            ->all();
        $data = [
            'template_id' => $this->template->id,
            'pillar_id' => $validated['questionPillarId'],
            'question' => $validated['questionText'],
            'description' => $validated['questionDescription'],
            'question_type' => $validated['questionType'],
            'weight' => $validated['questionWeight'],
            'is_required' => $validated['questionIsRequired'],
            'failure_recommendation' => $validated['questionFailureRecommendation'],
            'options' => $options ?: null,
            'depends_on_question_id' => $validated['questionDependsOnId'],
            'depends_on_answer' => $validated['questionDependsOnAnswer'] ?: null,
        ];

        if ($this->questionId) {
            AuditQuestion::where('template_id', $this->template->id)->findOrFail($this->questionId)->update($data);
        } else {
            $position = AuditQuestion::where('pillar_id', $validated['questionPillarId'])->max('position');
            AuditQuestion::create($data + ['position' => ((int) $position) + 1]);
        }

        $this->showQuestionModal = false;
        $this->resetQuestionForm();
    }

    public function deleteQuestion(int $id): void
    {
        AuditQuestion::where('template_id', $this->template->id)->findOrFail($id)->delete();
    }

    private function resetPillarForm(): void
    {
        $this->reset(['pillarId', 'pillarName', 'pillarDescription']);
        $this->pillarIcon = 'account_tree';
        $this->pillarTargetScore = 5;
        $this->resetValidation();
    }

    private function resetQuestionForm(): void
    {
        $this->reset([
            'questionId',
            'questionPillarId',
            'questionText',
            'questionDescription',
            'questionFailureRecommendation',
            'questionOptions',
            'questionDependsOnId',
            'questionDependsOnAnswer',
        ]);
        $this->questionType = 'scale_1_to_5';
        $this->questionWeight = 1;
        $this->questionIsRequired = true;
        $this->resetValidation();
    }

    public function render()
    {
        $pillars = $this->template->pillars()->with('questions')->get();
        $dependencyQuestions = $this->template->questions()->orderBy('position')->get();

        return view('auditpro::livewire.template-builder', compact('pillars', 'dependencyQuestions'));
    }
}
