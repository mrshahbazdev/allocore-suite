<?php

namespace Modules\BookIntelligence\Services;

use App\Models\User;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\ChallengeSubmission;
use Modules\BookIntelligence\Models\PracticalChallenge;
use Modules\ClusterForge\Services\AiService;
use RuntimeException;

class PracticalChallengeGenerator
{
    public function __construct(
        private readonly AiService $ai,
        private readonly ExpertiseProgressionService $expertiseService,
    ) {}

    /**
     * Generate an actionable business case study & practical challenge from a book.
     */
    public function generateForBook(Book $book): PracticalChallenge
    {
        $book->loadMissing(['author', 'mainTopic', 'analysis']);

        $context = [
            'title' => $book->title,
            'author' => $book->author?->name,
            'topic' => $book->mainTopic?->name,
            'short_summary' => $book->analysis?->short_summary,
            'takeaways' => $book->analysis?->key_takeaways,
            'frameworks' => $book->analysis?->frameworks,
            'recommendations' => $book->analysis?->actionable_recommendations,
        ];

        $prompt = <<<PROMPT
You are Allocore's Master Executive Coach & Simulation Director. Design a realistic, high-stakes practical business challenge / simulation assignment based on the frameworks from the book below.

Context:
PROMPT
        . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . <<<PROMPT

Requirements:
1. "scenario_description": A realistic operational situation (e.g. "You are hired as VP Sales at an 8-figure SaaS company with declining quota attainment and high CAC...").
2. "assignment_brief": Exact deliverables the employee must create and submit.
3. "deliverable_format": (e.g. "Written Strategy Memo (500-1000 words)", "Playbook & KPI Matrix").
4. "reflection_questions": 3 deep self-reflection questions for the employee.
5. "evaluation_rubric": 3-4 scoring criteria with expectations for Master, Proficient, and Needs Improvement.

Output rules:
- Return ONLY a valid JSON object matching the schema below.

Schema:
{
  "title": "string",
  "scenario_description": "string",
  "assignment_brief": "string",
  "deliverable_format": "string",
  "difficulty": "intermediate|advanced|expert",
  "reflection_questions": ["string"],
  "evaluation_rubric": [
    { "criteria": "string", "weight": "string", "standard": "string" }
  ]
}
PROMPT;

        $result = $this->ai->generateJson($prompt, 0.3);

        if (! isset($result['assignment_brief'])) {
            throw new RuntimeException('AI did not return a valid practical challenge.');
        }

        return PracticalChallenge::create([
            'team_id' => $book->team_id,
            'book_id' => $book->id,
            'role_id' => null,
            'title' => $result['title'] ?? "Operational Implementation Challenge: {$book->title}",
            'scenario_description' => $result['scenario_description'] ?? 'Apply the principles to resolve an operational challenge.',
            'assignment_brief' => $result['assignment_brief'],
            'deliverable_format' => $result['deliverable_format'] ?? 'Written Strategy Document',
            'difficulty' => $result['difficulty'] ?? 'intermediate',
            'reflection_questions' => $result['reflection_questions'] ?? [],
            'evaluation_rubric' => $result['evaluation_rubric'] ?? [],
        ]);
    }

    /**
     * AI-Assisted Evaluation of an employee's challenge submission.
     */
    public function gradeSubmission(ChallengeSubmission $submission): ChallengeSubmission
    {
        $challenge = $submission->challenge;

        $prompt = <<<PROMPT
You are Allocore's Senior Executive Evaluator. Grade the employee's challenge submission below against the evaluation rubric.

Challenge: "{$challenge->title}"
Scenario: "{$challenge->scenario_description}"
Brief: "{$challenge->assignment_brief}"

Employee Deliverable:
"{$submission->submission_text}"

Employee Reflection Answers:
PROMPT
        . json_encode($submission->reflection_answers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . <<<PROMPT

Output rules:
- Return ONLY a valid JSON object with:
  - "score": number from 0 to 100
  - "status": "graded" or "needs_revision" (passing is >= 75)
  - "feedback": constructive, detailed executive feedback and recommendations

Schema:
{
  "score": 85.0,
  "status": "graded|needs_revision",
  "feedback": "string"
}
PROMPT;

        $result = $this->ai->generateJson($prompt, 0.2);

        $score = (float) ($result['score'] ?? 80.0);
        $status = $score >= 75 ? ChallengeSubmission::STATUS_GRADED : ChallengeSubmission::STATUS_NEEDS_REVISION;

        $submission->update([
            'grade_score' => $score,
            'status' => $status,
            'evaluator_feedback' => $result['feedback'] ?? 'Good execution of core principles.',
        ]);

        if ($status === ChallengeSubmission::STATUS_GRADED && $submission->user) {
            $this->expertiseService->recordChallengeCompleted($submission->user, 200);
        }

        return $submission;
    }
}
