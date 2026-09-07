<?php

namespace Modules\BookIntelligence\Services;

use App\Models\User;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\CompetencyRole;
use Modules\BookIntelligence\Models\LearningPath;
use Modules\ClusterForge\Services\AiService;
use RuntimeException;

class PersonalizedLearningPathGenerator
{
    public function __construct(private readonly AiService $ai) {}

    /**
     * Generate an AI-sequenced personalized learning path for an employee.
     */
    public function generateForUser(User $user, CompetencyRole $targetRole, ?CompetencyRole $currentRole = null): LearningPath
    {
        $books = Book::query()->with(['author', 'mainTopic', 'analysis'])->get();

        $context = [
            'employee_name' => $user->name,
            'current_role' => $currentRole?->name ?? 'Junior Specialist',
            'target_role' => [
                'name' => $targetRole->name,
                'department' => $targetRole->department,
                'level' => $targetRole->level,
                'required_knowledge' => $targetRole->required_knowledge,
                'required_competencies' => $targetRole->required_competencies,
                'required_skills' => $targetRole->required_skills,
            ],
            'available_books' => $books->map(fn ($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author?->name,
                'topic' => $b->mainTopic?->name,
                'difficulty' => $b->difficulty,
            ])->values()->all(),
        ];

        $prompt = <<<PROMPT
You are Allocore's Chief Talent Development & Career Strategist. Design a structured, personalized learning path for an employee advancing toward a target role.

Context:
PROMPT
        . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . <<<PROMPT

Requirements:
1. Create a logical 4 to 6 step progression sequence.
2. For each step:
   - Provide a step title (e.g. "Step 1: Mastering Outbound Sales Fundamentals")
   - Assign the most relevant book from the available library (provide exact book_id).
   - Define the primary competency developed in this step.
   - Provide practical execution rationale (why this step is essential before moving to the next).

Output rules:
- Return ONLY a valid JSON object matching the schema below.

Schema:
{
  "title": "string",
  "ai_rationale": "string",
  "steps": [
    {
      "order": 1,
      "title": "string",
      "book_id": 1,
      "competency": "string",
      "rationale": "string"
    }
  ]
}
PROMPT;

        $result = $this->ai->generateJson($prompt, 0.3);

        if (! isset($result['steps']) || ! is_array($result['steps'])) {
            throw new RuntimeException('AI did not return valid learning path steps.');
        }

        $steps = [];
        foreach ($result['steps'] as $idx => $step) {
            $bookId = isset($step['book_id']) && Book::where('id', $step['book_id'])->exists()
                ? $step['book_id']
                : $books->first()?->id;

            $steps[] = [
                'order' => $idx + 1,
                'title' => $step['title'] ?? "Step " . ($idx + 1),
                'book_id' => $bookId,
                'competency' => $step['competency'] ?? 'Core Knowledge',
                'rationale' => $step['rationale'] ?? '',
                'status' => $idx === 0 ? 'in_progress' : 'pending',
                'completed_at' => null,
            ];
        }

        $path = LearningPath::create([
            'team_id' => $user->currentTeam?->id ?? 1,
            'user_id' => $user->id,
            'target_role_id' => $targetRole->id,
            'title' => $result['title'] ?? "Personalized Roadmap to {$targetRole->name}",
            'status' => LearningPath::STATUS_ACTIVE,
            'steps' => $steps,
            'progress_percent' => 0,
            'ai_rationale' => $result['ai_rationale'] ?? "Structured development roadmap aligned with {$targetRole->name} competency standards.",
        ]);

        return $path;
    }
}
