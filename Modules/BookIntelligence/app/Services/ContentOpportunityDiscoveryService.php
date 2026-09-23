<?php

namespace Modules\BookIntelligence\Services;

use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\ContentOpportunity;
use Modules\BookIntelligence\Models\KnowledgeGap;
use Modules\BookIntelligence\Models\SearchQuery;
use Modules\ClusterForge\Services\AiService;
use RuntimeException;

class ContentOpportunityDiscoveryService
{
    public function __construct(private readonly AiService $ai) {}

    /**
     * Discover high-traffic SEO and thought leadership opportunities from library & gaps.
     *
     * @return array<int, ContentOpportunity>
     */
    public function discoverOpportunities(?Book $specificBook = null): array
    {
        $books = $specificBook
            ? collect([$specificBook])
            : Book::query()->with(['author', 'mainTopic', 'analysis'])->take(10)->get();

        $recentQueries = SearchQuery::query()->latest()->take(15)->pluck('query')->all();
        $gaps = KnowledgeGap::query()->open()->take(10)->pluck('query')->all();

        $context = [
            'books' => $books->map(fn ($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author?->name,
                'topic' => $b->mainTopic?->name,
                'takeaways' => $b->analysis?->key_takeaways,
            ])->values()->all(),
            'internal_user_searches' => $recentQueries,
            'unresolved_knowledge_gaps' => $gaps,
        ];

        $prompt = <<<PROMPT
You are Allocore's Chief Content & SEO Growth Strategist. Analyze our organizational knowledge, book intelligence, user searches, and unresolved knowledge gaps to identify 8 to 12 high-intent, high-traffic content marketing opportunities in German (auf Deutsch für den DACH-Markt).

Content Types:
- blog (In-depth 8-section tactical guides)
- faq (Direct problem-solution articles)
- whitepaper (Comprehensive industry benchmarks and frameworks)
- newsletter (Executive briefings and strategic memos)
- linkedin (Viral hooks, carousels, and founder takeaways)

Language Requirement:
- Write all titles, target keywords, target audiences, and angle hooks strictly in GERMAN (Business Deutsch).

Output rules:
- Return ONLY a valid JSON array of objects with the exact schema below.

Schema:
[
  {
    "book_id": 1,
    "title": "string (in German)",
    "target_keyword": "string (in German)",
    "content_type": "blog|faq|whitepaper|newsletter|linkedin",
    "search_intent": "informational|commercial|transactional",
    "estimated_demand": "high|medium|niche",
    "target_audience": ["string (in German)"],
    "angle_hook": "string (in German)"
  }
]

Context:
PROMPT
        . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $result = $this->ai->generateJson($prompt, 0.3);
        $items = isset($result[0]) ? $result : ($result['opportunities'] ?? $result['items'] ?? []);

        if (! is_array($items) || count($items) === 0) {
            throw new RuntimeException('AI did not return valid content opportunities.');
        }

        $created = [];
        $teamId = $specificBook?->team_id ?? auth()->user()?->currentTeam?->id ?? 1;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $bookId = isset($item['book_id']) && Book::where('id', $item['book_id'])->exists()
                ? $item['book_id']
                : ($specificBook?->id ?? $books->first()?->id);

            $created[] = ContentOpportunity::create([
                'team_id' => $teamId,
                'user_id' => auth()->id(),
                'book_id' => $bookId,
                'title' => $title,
                'target_keyword' => trim((string) ($item['target_keyword'] ?? '')),
                'content_type' => in_array($item['content_type'] ?? '', ['blog', 'faq', 'whitepaper', 'newsletter', 'linkedin'], true) ? $item['content_type'] : 'blog',
                'search_intent' => in_array($item['search_intent'] ?? '', ['informational', 'commercial', 'transactional'], true) ? $item['search_intent'] : 'informational',
                'estimated_demand' => in_array($item['estimated_demand'] ?? '', ['high', 'medium', 'niche'], true) ? $item['estimated_demand'] : 'high',
                'target_audience' => is_array($item['target_audience'] ?? null) ? $item['target_audience'] : ['Founders', 'Operations Leaders'],
                'angle_hook' => trim((string) ($item['angle_hook'] ?? '')),
                'status' => ContentOpportunity::STATUS_IDEA,
            ]);
        }

        return $created;
    }
}
