<?php

namespace Modules\BookIntelligence\Services;

use App\Models\BlogCategory;
use App\Models\Post;
use Illuminate\Support\Str;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\ContentOpportunity;
use Modules\BookIntelligence\Models\GeneratedBlog;
use Modules\ClusterForge\Services\AiService;
use RuntimeException;

class AutomatedBlogGenerator
{
    public function __construct(
        private readonly AiService $ai,
        private readonly AffiliateRecommendationService $affiliateService,
    ) {}

    /**
     * Generate a full 8-section blog article from a content opportunity or book.
     */
    public function generate(Book $book, ?ContentOpportunity $opportunity = null, ?string $customTopic = null): GeneratedBlog
    {
        $book->loadMissing(['author', 'mainTopic', 'analysis']);

        $topicTitle = $opportunity?->title ?: ($customTopic ?: "How to Master {$book->mainTopic?->name} Using Insights from {$book->title}");
        $targetKeyword = $opportunity?->target_keyword ?: ($book->mainTopic?->name ?? 'Business Optimization');

        $prompt = $this->buildPrompt($book, $topicTitle, $targetKeyword, $opportunity);
        $result = $this->ai->generateJson($prompt, 0.3);

        $normalized = $this->normalize($result, $book);

        $htmlContent = $this->buildFullHtml($normalized, $book);

        $slug = Str::slug($normalized['title']);
        $uniqueSlug = $slug . '-' . Str::random(5);

        $generated = GeneratedBlog::create([
            'team_id' => $book->team_id,
            'user_id' => auth()->id() ?? $book->user_id,
            'book_id' => $book->id,
            'opportunity_id' => $opportunity?->id,
            'title' => $normalized['title'],
            'slug' => $uniqueSlug,
            'target_keyword' => $targetKeyword,
            'meta_description' => $normalized['meta_description'],
            'section_1_problem' => $normalized['section_1_problem'],
            'section_2_root_causes' => $normalized['section_2_root_causes'],
            'section_3_solutions' => $normalized['section_3_solutions'],
            'section_4_implementation' => $normalized['section_4_implementation'],
            'section_5_common_mistakes' => $normalized['section_5_common_mistakes'],
            'section_6_summary' => $normalized['section_6_summary'],
            'section_7_cta' => $normalized['section_7_cta'],
            'section_8_recommended_book' => $normalized['section_8_recommended_book'],
            'full_html_content' => $htmlContent,
            'status' => GeneratedBlog::STATUS_DRAFT,
        ]);

        if ($opportunity) {
            $opportunity->update(['status' => ContentOpportunity::STATUS_GENERATED]);
        }

        return $generated;
    }

    /**
     * Publish or sync a generated blog post directly to the Allocore CMS Blog.
     */
    public function publishToCms(GeneratedBlog $blog, ?int $categoryId = null): Post
    {
        $category = $categoryId ? BlogCategory::find($categoryId) : BlogCategory::first();

        $post = Post::updateOrCreate(
            ['slug' => $blog->slug],
            [
                'user_id' => $blog->user_id ?? auth()->id() ?? 1,
                'category_id' => $category?->id,
                'title' => $blog->title,
                'excerpt' => $blog->meta_description ?: Str::limit(strip_tags($blog->section_1_problem), 200),
                'body' => $blog->full_html_content,
                'meta_title' => $blog->title . ' | Allocore',
                'meta_description' => $blog->meta_description,
                'meta_keywords' => $blog->target_keyword,
                'is_published' => true,
                'published_at' => now(),
            ]
        );

        $blog->update([
            'post_id' => $post->id,
            'status' => GeneratedBlog::STATUS_PUBLISHED,
        ]);

        if ($blog->opportunity) {
            $blog->opportunity->update([
                'status' => ContentOpportunity::STATUS_PUBLISHED,
                'generated_post_id' => $post->id,
            ]);
        }

        return $post;
    }

    private function buildPrompt(Book $book, string $topic, string $keyword, ?ContentOpportunity $opportunity): string
    {
        $bookContext = [
            'title' => $book->title,
            'author' => $book->author?->name,
            'main_topic' => $book->mainTopic?->name,
            'short_summary' => $book->analysis?->short_summary,
            'takeaways' => $book->analysis?->key_takeaways,
            'frameworks' => $book->analysis?->frameworks,
        ];

        return <<<PROMPT
You are Allocore's Master Thought Leadership Author. Write a comprehensive, highly authoritative, and engaging 8-section blog article on the topic below.

Topic: "{$topic}"
Target SEO Keyword: "{$keyword}"
Angle/Hook: "{$opportunity?->angle_hook}"

Source Book Knowledge:
PROMPT
        . json_encode($bookContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . <<<PROMPT

MANDATORY 8-SECTION BLOG STRUCTURE:
1. Section 1: Problem — Describe the exact high-stakes challenge and frustration faced by modern business operators/leaders.
2. Section 2: Root Causes — Explain why this problem happens and the hidden pitfalls behind it.
3. Section 3: Solutions — Present clear, strategic approaches to solve the problem.
4. Section 4: Practical Implementation — Provide step-by-step, actionable execution instructions with frameworks from the book.
5. Section 5: Common Mistakes — Highlight 3 to 5 typical errors teams make when attempting to solve this.
6. Section 6: Summary — Summarize the core lessons and strategic takeaways.
7. Section 7: Call to Action (CTA) — Clear call-to-action inviting readers to audit their operations using Allocore Suite tools.
8. Section 8: Recommended Book — A compelling review and recommendation of "{$book->title}" by {$book->author?->name}, explaining exactly why the reader must read this book to master the topic.

Output rules:
- Return ONLY a valid JSON object matching the schema below.
- Format each section in clean, rich HTML (using <p>, <h3>, <ul>, <li>, <strong>, <blockquote>).

Schema:
{
  "title": "string",
  "meta_description": "string (150-160 chars)",
  "section_1_problem": "html string",
  "section_2_root_causes": "html string",
  "section_3_solutions": "html string",
  "section_4_implementation": "html string",
  "section_5_common_mistakes": "html string",
  "section_6_summary": "html string",
  "section_7_cta": "html string",
  "section_8_recommended_book": {
    "title": "{$book->title}",
    "author": "{$book->author?->name}",
    "why_recommended": "string",
    "reading_time_value": "string"
  }
}
PROMPT;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function normalize(array $result, Book $book): array
    {
        if (! isset($result['title']) || ! isset($result['section_1_problem'])) {
            throw new RuntimeException('AI generated an incomplete blog structure.');
        }

        return [
            'title' => trim((string) $result['title']),
            'meta_description' => trim((string) ($result['meta_description'] ?? '')),
            'section_1_problem' => trim((string) $result['section_1_problem']),
            'section_2_root_causes' => trim((string) ($result['section_2_root_causes'] ?? '')),
            'section_3_solutions' => trim((string) ($result['section_3_solutions'] ?? '')),
            'section_4_implementation' => trim((string) ($result['section_4_implementation'] ?? '')),
            'section_5_common_mistakes' => trim((string) ($result['section_5_common_mistakes'] ?? '')),
            'section_6_summary' => trim((string) ($result['section_6_summary'] ?? '')),
            'section_7_cta' => trim((string) ($result['section_7_cta'] ?? '')),
            'section_8_recommended_book' => is_array($result['section_8_recommended_book'] ?? null)
                ? $result['section_8_recommended_book']
                : [
                    'title' => $book->title,
                    'author' => $book->author?->name ?? 'Author',
                    'why_recommended' => "Deep dive into the core methodology of {$book->title}.",
                ],
        ];
    }

    private function buildFullHtml(array $data, Book $book): string
    {
        $affiliateCardHtml = $this->affiliateService->renderRecommendationBoxHtml($book, $data['section_8_recommended_book']['why_recommended'] ?? null);

        return <<<HTML
<div class="allocore-blog-article">
    <section class="blog-section mb-8">
        <h2>The Challenge</h2>
        {$data['section_1_problem']}
    </section>

    <section class="blog-section mb-8">
        <h2>Root Causes: Why This Happens</h2>
        {$data['section_2_root_causes']}
    </section>

    <section class="blog-section mb-8">
        <h2>Strategic Solutions</h2>
        {$data['section_3_solutions']}
    </section>

    <section class="blog-section mb-8">
        <h2>Practical Step-by-Step Implementation</h2>
        {$data['section_4_implementation']}
    </section>

    <section class="blog-section mb-8">
        <h2>Common Mistakes to Avoid</h2>
        {$data['section_5_common_mistakes']}
    </section>

    <section class="blog-section mb-8">
        <h2>Summary & Key Takeaways</h2>
        {$data['section_6_summary']}
    </section>

    <section class="blog-section mb-8 p-6 bg-slate-50 rounded-2xl border border-slate-200">
        <h3>Accelerate Your Execution</h3>
        {$data['section_7_cta']}
    </section>

    <section class="blog-section mt-12">
        {$affiliateCardHtml}
    </section>
</div>
HTML;
    }
}
