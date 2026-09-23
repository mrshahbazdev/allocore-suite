<?php

namespace Modules\BookIntelligence\Services;

use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\RepurposedBundle;
use Modules\ClusterForge\Services\AiService;
use RuntimeException;

class BookRepurposingEngine
{
    public function __construct(private readonly AiService $ai) {}

    /**
     * Repurpose a book into a comprehensive 6-format content multiplier bundle.
     */
    public function generateBundle(Book $book): RepurposedBundle
    {
        $book->loadMissing(['author', 'mainTopic', 'analysis']);

        $context = [
            'title' => $book->title,
            'author' => $book->author?->name,
            'topic' => $book->mainTopic?->name,
            'short_summary' => $book->analysis?->short_summary,
            'long_summary' => $book->analysis?->long_summary,
            'takeaways' => $book->analysis?->key_takeaways,
            'frameworks' => $book->analysis?->frameworks,
            'recommendations' => $book->analysis?->actionable_recommendations,
        ];

        $prompt = <<<PROMPT
You are Allocore's Content Multiplier & Omnichannel Repurposing Director. Transform the book context below into a massive library of high-impact marketing and thought leadership content assets in German (auf Deutsch für den deutschsprachigen DACH-Markt).

Language Requirement:
- IMPORTANT: All 6 content formats below MUST be written entirely in professional Business German (auf Deutsch).

Output Requirements (in German):
1. "blog_articles": 20 compelling, SEO-rich German blog article titles with target keywords and core angles.
2. "linkedin_posts": 50 high-engagement German LinkedIn post drafts covering viral hooks, framework breakdowns, founder lessons, and carousels.
3. "faq_articles": 20 high-intent German FAQ questions with concise 2-sentence answers.
4. "checklists": 10 actionable operational German checklists with step-by-step items.
5. "practical_guides": 10 implementation German playbooks with clear execution steps.
6. "whitepaper_concepts": 5 executive German whitepaper titles, target audiences, and executive summaries.

Output rules:
- Return ONLY a valid JSON object matching the schema below.

Schema:
{
  "blog_articles": [
    { "title": "string (in German)", "keyword": "string (in German)", "angle": "string (in German)" }
  ],
  "linkedin_posts": [
    { "hook": "string (in German)", "content": "string (in German)", "type": "story|breakdown|framework|carousel|contrarian" }
  ],
  "faq_articles": [
    { "question": "string (in German)", "answer": "string (in German)" }
  ],
  "checklists": [
    { "title": "string (in German)", "items": ["string (in German)"] }
  ],
  "practical_guides": [
    { "title": "string (in German)", "steps": ["string (in German)"], "outcome": "string (in German)" }
  ],
  "whitepaper_concepts": [
    { "title": "string (in German)", "target_audience": "string (in German)", "summary": "string (in German)" }
  ]
}

Book Context:
PROMPT
        . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $result = $this->ai->generateJson($prompt, 0.3);

        $bundle = RepurposedBundle::updateOrCreate(
            [
                'team_id' => $book->team_id,
                'book_id' => $book->id,
            ],
            [
                'user_id' => auth()->id() ?? $book->user_id,
                'blog_articles' => $result['blog_articles'] ?? [],
                'linkedin_posts' => $result['linkedin_posts'] ?? [],
                'faq_articles' => $result['faq_articles'] ?? [],
                'checklists' => $result['checklists'] ?? [],
                'practical_guides' => $result['practical_guides'] ?? [],
                'whitepaper_concepts' => $result['whitepaper_concepts'] ?? [],
            ]
        );

        return $bundle;
    }
}
