<?php

namespace Modules\BookIntelligence\Services;

use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\QuestionMapping;
use Modules\ClusterForge\Services\AiService;
use RuntimeException;

class QuestionMappingGenerator
{
    public function __construct(private readonly AiService $ai) {}

    /**
     * Generate and persist question mappings, FAQs, and when-to-read triggers for a book.
     *
     * @return array<int, QuestionMapping>
     */
    public function generateForBook(Book $book): array
    {
        $book->loadMissing(['author', 'publisher', 'mainTopic', 'subtopics', 'analysis']);

        $result = $this->ai->generateJson($this->prompt($book), 0.3);
        $normalized = $this->normalize($result);

        $mappings = [];

        // Delete existing auto-generated question mappings for this book if any
        $book->questionMappings()->delete();

        foreach ($normalized as $item) {
            $mappings[] = QuestionMapping::create([
                'team_id' => $book->team_id,
                'book_id' => $book->id,
                'user_id' => auth()->id() ?? $book->user_id,
                'question' => $item['question'],
                'answer_excerpt' => $item['answer_excerpt'],
                'problem_statement' => $item['problem_statement'],
                'target_audience' => $item['target_audience'],
                'when_to_read_trigger' => $item['when_to_read_trigger'],
                'category' => $item['category'] ?? $book->mainTopic?->name ?? 'General',
                'priority' => $item['priority'] ?? 1,
                'is_active' => true,
            ]);
        }

        return $mappings;
    }

    private function prompt(Book $book): string
    {
        $language = match ($book->language) {
            'en' => 'English',
            default => 'German',
        };

        $context = [
            'title' => $book->title,
            'author' => $book->author?->name,
            'publisher' => $book->publisher?->name,
            'publication_year' => $book->publication_year,
            'difficulty' => $book->difficulty,
            'description' => $book->description,
            'main_topic' => $book->mainTopic?->name,
            'subtopics' => $book->subtopics->pluck('name')->values()->all(),
            'relevant_roles' => $book->relevant_roles ?? [],
            'short_summary' => $book->analysis?->short_summary,
            'long_summary' => $book->analysis?->long_summary,
            'key_takeaways' => $book->analysis?->key_takeaways,
            'frameworks' => $book->analysis?->frameworks,
        ];

        $template = <<<'PROMPT'
You are Allocore's Chief Learning & Knowledge Architect. Analyze the book context below and extract an authoritative Question-to-Knowledge Mapping database.

Requirements:
1. Identify 5 to 10 specific, high-intent operational/business questions that this book answers in depth. (e.g. "How do I scale a sales organization?", "How can I reduce DSO?", "How do I design compensation for SDRs?").
2. For each question:
   - Provide a direct, actionable answer excerpt synthesizing the book's core solution (2-4 sentences).
   - Identify the specific business/operational problem it solves.
   - Specify target audience / job roles (e.g. ["Sales Leaders", "Founders", "RevOps", "CFO"]).
   - Define a concrete "When to Read" trigger or symptom (e.g. "When team size exceeds 10 reps and sales pipeline conversion drops below 15%%").
   - Categorize the domain (e.g. "Sales", "Finance", "Leadership", "Operations", "Product").

Output rules:
- Write user-facing values in %s.
- Return only one valid JSON array of objects using the exact schema below.

Schema:
[
  {
    "question": "string",
    "answer_excerpt": "string",
    "problem_statement": "string",
    "target_audience": ["string"],
    "when_to_read_trigger": "string",
    "category": "string",
    "priority": 1
  }
]

Book context:
%s
PROMPT;

        return sprintf(
            $template,
            $language,
            json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @param array<string, mixed>|array<int, mixed> $result
     * @return array<int, array<string, mixed>>
     */
    private function normalize(array $result): array
    {
        $items = isset($result[0]) ? $result : ($result['questions'] ?? $result['items'] ?? []);

        if (! is_array($items) || count($items) === 0) {
            throw new RuntimeException('AI did not return valid question mappings.');
        }

        $normalized = [];

        foreach ($items as $idx => $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer_excerpt'] ?? $item['answer'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $audience = is_array($item['target_audience'] ?? null)
                ? collect($item['target_audience'])
                    ->filter(fn ($a) => is_string($a) && trim($a) !== '')
                    ->map(fn ($a) => trim($a))
                    ->values()
                    ->all()
                : ['Founders', 'Managers'];

            $normalized[] = [
                'question' => $question,
                'answer_excerpt' => $answer,
                'problem_statement' => trim((string) ($item['problem_statement'] ?? 'Operational inefficiency')),
                'target_audience' => $audience,
                'when_to_read_trigger' => trim((string) ($item['when_to_read_trigger'] ?? 'When facing scaling challenges')),
                'category' => trim((string) ($item['category'] ?? 'General')),
                'priority' => (int) ($item['priority'] ?? ($idx + 1)),
            ];
        }

        if (empty($normalized)) {
            throw new RuntimeException('No usable question mappings could be extracted.');
        }

        return $normalized;
    }
}
