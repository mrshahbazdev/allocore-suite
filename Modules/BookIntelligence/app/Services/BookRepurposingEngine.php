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
You are Allocore's Content Multiplier & Omnichannel Repurposing Director. Transform the book context below into a massive library of high-impact marketing and thought leadership content assets.

Output Requirements:
1. "blog_articles": 20 compelling, SEO-rich blog article titles with target keywords and core angles.
2. "linkedin_posts": 50 high-engagement LinkedIn post drafts covering viral hooks, framework breakdowns, founder lessons, and carousels.
3. "faq_articles": 20 high-intent FAQ questions with concise 2-sentence answers.
4. "checklists": 10 actionable operational checklists with step-by-step items.
5. "practical_guides": 10 implementation playbooks with clear execution steps.
6. "whitepaper_concepts": 5 executive whitepaper titles, target audiences, and executive summaries.

Output rules:
- Return ONLY a valid JSON object matching the schema below.

Schema:
{
  "blog_articles": [
    { "title": "string", "keyword": "string", "angle": "string" }
  ],
  "linkedin_posts": [
    { "hook": "string", "content": "string", "type": "story|breakdown|framework|carousel|contrarian" }
  ],
  "faq_articles": [
    { "question": "string", "answer": "string" }
  ],
  "checklists": [
    { "title": "string", "items": ["string"] }
  ],
  "practical_guides": [
    { "title": "string", "steps": ["string"], "outcome": "string" }
  ],
  "whitepaper_concepts": [
    { "title": "string", "target_audience": "string", "summary": "string" }
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
