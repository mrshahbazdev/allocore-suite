<?php

namespace Modules\BookIntelligence\Services;

use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\BookAnalysis;
use Modules\ClusterForge\Services\AiService;
use RuntimeException;

class BookAnalysisGenerator
{
    public function __construct(private readonly AiService $ai) {}

    public function generate(BookAnalysis $analysis): void
    {
        $book = $analysis->book()
            ->with(['author', 'publisher', 'mainTopic', 'subtopics'])
            ->firstOrFail();

        $result = $this->ai->generateJson($this->prompt($book, $analysis->source_material), 0.3);
        $normalized = $this->normalize($result);

        $analysis->update($normalized + [
            'status' => BookAnalysis::STATUS_COMPLETED,
            'source_fingerprint' => $book->analysisFingerprint($analysis->source_material),
            'provider' => $this->ai->configuredProviderName(),
            'generated_at' => now(),
            'error' => null,
        ]);
    }

    private function prompt(Book $book, ?string $sourceMaterial): string
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
            'source_material' => filled($sourceMaterial) ? $sourceMaterial : null,
        ];

        $template = <<<'PROMPT'
You are Allocore's senior learning strategist. Analyze the book context below and return practical organizational learning intelligence.

Accuracy rules:
- Identify the exact book from its metadata before analyzing it.
- Prefer the supplied description and source material.
- Use only high-confidence knowledge. Never invent quotations, page numbers, case studies, statistics, framework names, or author claims.
- If the identity or content is uncertain, state the limitation plainly inside the summaries.
- Distinguish the author's ideas from your implementation advice.
- Write for busy professionals using concise, specific language.

Output rules:
- Write every user-facing value in %s.
- Return only one valid JSON object using the exact schema below.
- short_summary should be a 1–2 minute read.
- long_summary should explain the central argument, important concepts, reasoning, and practical implications.
- key_takeaways should cover main insights, core lessons, and strategic value.
- frameworks may include only genuine models, frameworks, processes, checklists, or methodologies supported by the book context.
- actionable_recommendations must be immediately executable and should not merely repeat the takeaways.

Schema:
{
  "short_summary": "string",
  "long_summary": "string",
  "key_takeaways": [
    {
      "title": "string",
      "insight": "string",
      "why_it_matters": "string"
    }
  ],
  "frameworks": [
    {
      "name": "string",
      "type": "model|framework|process|checklist|methodology",
      "description": "string",
      "steps": ["string"],
      "when_to_use": "string"
    }
  ],
  "actionable_recommendations": [
    {
      "action": "string",
      "why": "string",
      "first_step": "string",
      "priority": "high|medium|low"
    }
  ]
}

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
     * @param  array<string, mixed>|array<int, mixed>  $result
     * @return array<string, mixed>
     */
    private function normalize(array $result): array
    {
        $shortSummary = $this->requiredText($result['short_summary'] ?? null, 'short_summary');
        $longSummary = $this->requiredText($result['long_summary'] ?? null, 'long_summary');

        return [
            'short_summary' => $shortSummary,
            'long_summary' => $longSummary,
            'key_takeaways' => $this->normalizeTakeaways($result['key_takeaways'] ?? []),
            'frameworks' => $this->normalizeFrameworks($result['frameworks'] ?? []),
            'actionable_recommendations' => $this->normalizeRecommendations($result['actionable_recommendations'] ?? []),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function normalizeTakeaways(mixed $items): array
    {
        if (! is_array($items)) {
            throw new RuntimeException('AI analysis returned invalid key takeaways.');
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = $this->optionalText($item['title'] ?? null);
            $insight = $this->optionalText($item['insight'] ?? null);

            if ($title === null || $insight === null) {
                continue;
            }

            $normalized[] = [
                'title' => $title,
                'insight' => $insight,
                'why_it_matters' => $this->optionalText($item['why_it_matters'] ?? null) ?? '',
            ];
        }

        if ($normalized === []) {
            throw new RuntimeException('AI analysis did not return usable key takeaways.');
        }

        return array_slice($normalized, 0, 12);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeFrameworks(mixed $items): array
    {
        if (! is_array($items)) {
            throw new RuntimeException('AI analysis returned invalid frameworks.');
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $name = $this->optionalText($item['name'] ?? null);
            $description = $this->optionalText($item['description'] ?? null);

            if ($name === null || $description === null) {
                continue;
            }

            $steps = is_array($item['steps'] ?? null)
                ? collect($item['steps'])
                    ->filter(fn (mixed $step) => is_string($step) && trim($step) !== '')
                    ->map(fn (string $step) => trim($step))
                    ->take(12)
                    ->values()
                    ->all()
                : [];

            $normalized[] = [
                'name' => $name,
                'type' => $this->frameworkType($item['type'] ?? null),
                'description' => $description,
                'steps' => $steps,
                'when_to_use' => $this->optionalText($item['when_to_use'] ?? null) ?? '',
            ];
        }

        return array_slice($normalized, 0, 8);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function normalizeRecommendations(mixed $items): array
    {
        if (! is_array($items)) {
            throw new RuntimeException('AI analysis returned invalid recommendations.');
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $action = $this->optionalText($item['action'] ?? null);
            $firstStep = $this->optionalText($item['first_step'] ?? null);

            if ($action === null || $firstStep === null) {
                continue;
            }

            $priority = strtolower($this->optionalText($item['priority'] ?? null) ?? 'medium');

            $normalized[] = [
                'action' => $action,
                'why' => $this->optionalText($item['why'] ?? null) ?? '',
                'first_step' => $firstStep,
                'priority' => in_array($priority, ['high', 'medium', 'low'], true) ? $priority : 'medium',
            ];
        }

        if ($normalized === []) {
            throw new RuntimeException('AI analysis did not return usable actionable recommendations.');
        }

        return array_slice($normalized, 0, 12);
    }

    private function requiredText(mixed $value, string $field): string
    {
        $text = $this->optionalText($value);

        if ($text === null) {
            throw new RuntimeException("AI analysis did not return a usable {$field}.");
        }

        return $text;
    }

    private function optionalText(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function frameworkType(mixed $value): string
    {
        $type = strtolower($this->optionalText($value) ?? 'framework');

        return in_array($type, ['model', 'framework', 'process', 'checklist', 'methodology'], true)
            ? $type
            : 'framework';
    }
}
