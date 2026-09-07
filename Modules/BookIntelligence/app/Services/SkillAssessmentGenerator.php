<?php

namespace Modules\BookIntelligence\Services;

use App\Models\User;
use Modules\BookIntelligence\Models\AssessmentSubmission;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\CompetencyRole;
use Modules\BookIntelligence\Models\SkillAssessment;
use Modules\ClusterForge\Services\AiService;
use RuntimeException;

class SkillAssessmentGenerator
{
    public function __construct(
        private readonly AiService $ai,
        private readonly ExpertiseProgressionService $expertiseService,
    ) {}

    /**
     * Generate an AI scenario-based skill assessment for a Book or Role.
     */
    public function generateForBook(Book $book): SkillAssessment
    {
        $book->loadMissing(['author', 'mainTopic', 'analysis']);

        $context = [
            'title' => $book->title,
            'author' => $book->author?->name,
            'topic' => $book->mainTopic?->name,
            'short_summary' => $book->analysis?->short_summary,
            'takeaways' => $book->analysis?->key_takeaways,
            'frameworks' => $book->analysis?->frameworks,
        ];

        $prompt = <<<PROMPT
You are Allocore's Chief Assessment & Knowledge Evaluation Psychometrician. Create a rigorous, scenario-based 5-question skill assessment based on the book context below.

Context:
PROMPT
        . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . <<<PROMPT

Requirements:
1. Write 5 challenging scenario-based questions that test practical comprehension and decision-making (not mere trivial recall).
2. For each question:
   - Provide realistic business context.
   - Provide 4 distinct options.
   - Specify "correct_index" (0, 1, 2, or 3).
   - Provide an in-depth "explanation" explaining why the correct choice is superior and why others fail.
   - Tag the specific "competency" evaluated.

Output rules:
- Return ONLY a valid JSON object matching the schema below.

Schema:
{
  "title": "string",
  "description": "string",
  "passing_score": 70,
  "questions": [
    {
      "id": 1,
      "question": "string",
      "options": ["string", "string", "string", "string"],
      "correct_index": 0,
      "explanation": "string",
      "competency": "string"
    }
  ]
}
PROMPT;

        $result = $this->ai->generateJson($prompt, 0.2);

        if (! isset($result['questions']) || ! is_array($result['questions'])) {
            throw new RuntimeException('AI did not return valid assessment questions.');
        }

        return SkillAssessment::create([
            'team_id' => $book->team_id,
            'book_id' => $book->id,
            'role_id' => null,
            'title' => $result['title'] ?? "Knowledge & Competency Assessment: {$book->title}",
            'description' => $result['description'] ?? "Evaluate your practical mastery of frameworks and principles from {$book->title}.",
            'questions' => $result['questions'],
            'passing_score' => (int) ($result['passing_score'] ?? 70),
            'time_limit_minutes' => 15,
        ]);
    }

    /**
     * Grade a user's assessment submission and calculate strengths vs gaps.
     */
    public function evaluateSubmission(SkillAssessment $assessment, array $userAnswers, User $user): AssessmentSubmission
    {
        $questions = $assessment->questions ?? [];
        $totalQuestions = count($questions);

        if ($totalQuestions === 0) {
            throw new RuntimeException('Assessment has no questions.');
        }

        $correctCount = 0;
        $strengths = [];
        $gaps = [];

        foreach ($questions as $idx => $q) {
            $userChoice = $userAnswers[$idx] ?? null;
            $correctChoice = $q['correct_index'] ?? 0;
            $comp = $q['competency'] ?? 'Core Knowledge';

            if ((int) $userChoice === (int) $correctChoice) {
                $correctCount++;
                $strengths[] = $comp;
            } else {
                $gaps[] = [
                    'competency' => $comp,
                    'question' => $q['question'],
                    'explanation' => $q['explanation'],
                ];
            }
        }

        $score = round(($correctCount / $totalQuestions) * 100, 2);
        $passed = $score >= $assessment->passing_score;

        $feedback = $passed
            ? "Congratulations! You demonstrated strong mastery of {$assessment->title} with a score of {$score}%."
            : "You scored {$score}%. Review the explanations below and re-attempt to solidify your mastery.";

        $submission = AssessmentSubmission::create([
            'team_id' => $assessment->team_id,
            'assessment_id' => $assessment->id,
            'user_id' => $user->id,
            'answers' => $userAnswers,
            'score' => $score,
            'passed' => $passed,
            'strengths' => array_values(array_unique($strengths)),
            'gaps' => $gaps,
            'feedback' => $feedback,
        ]);

        if ($passed) {
            $this->expertiseService->recordAssessmentPassed($user, 100);
        }

        return $submission;
    }
}
