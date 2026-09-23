<?php

namespace Modules\BookIntelligence\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Modules\BookIntelligence\Models\Author;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\KnowledgeGap;
use Modules\BookIntelligence\Models\Topic;
use Modules\ClusterForge\Services\AiService;
use Throwable;

class KnowledgeGapService
{
    public function __construct(private readonly AiService $ai) {}

    /**
     * Record or increment an unresolved knowledge gap.
     */
    public function recordGap(string $query, ?User $user = null): KnowledgeGap
    {
        $teamId = $user?->currentTeam?->id ?? auth()->user()?->currentTeam?->id ?? 1;

        $gap = KnowledgeGap::query()
            ->where('team_id', $teamId)
            ->where('query', 'like', $query)
            ->first();

        if ($gap) {
            $gap->increment('search_count');
            return $gap;
        }

        $gap = KnowledgeGap::create([
            'team_id' => $teamId,
            'user_id' => $user?->id ?? auth()->id(),
            'query' => $query,
            'status' => KnowledgeGap::STATUS_OPEN,
            'search_count' => 1,
            'suggested_books' => [],
        ]);

        // Automatically query AI for book recommendations to fill this gap
        try {
            $this->generateRecommendationsForGap($gap);
        } catch (Throwable) {
            // Keep gap recorded even if background recommendation call fails
        }

        return $gap;
    }

    /**
     * Generate authoritative external book recommendations to fill a knowledge gap.
     *
     * @return array<int, array<string, string>>
     */
    public function generateRecommendationsForGap(KnowledgeGap $gap): array
    {
        $prompt = <<<PROMPT
You are Allocore's Chief Knowledge Strategist. Our team searched for the following question/topic, but our internal book library does not contain sufficient knowledge to answer it:

Unresolved Question: "{$gap->query}"

Recommend 3 to 5 world-class, published business, leadership, engineering, or management books that directly and authoritatively address this knowledge gap.

Output rules:
- Return ONLY a valid JSON array of objects with the exact schema below.
- Do NOT invent fictional books; recommend real, highly-regarded published books (e.g. by authors like Andy Grove, Eliyahu Goldratt, Aaron Ross, Ray Dalio, Eric Ries, etc.).

Schema:
[
  {
    "title": "string",
    "author": "string",
    "topic": "string",
    "difficulty": "beginner|intermediate|advanced|expert",
    "reason_for_recommendation": "string",
    "key_concept": "string"
  }
]
PROMPT;

        $result = $this->ai->generateJson($prompt, 0.2);

        $items = isset($result[0]) ? $result : ($result['books'] ?? $result['recommendations'] ?? []);
        $suggestions = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $author = trim((string) ($item['author'] ?? ''));

            if ($title === '' || $author === '') {
                continue;
            }

            $suggestions[] = [
                'title' => $title,
                'author' => $author,
                'topic' => trim((string) ($item['topic'] ?? 'General Business')),
                'difficulty' => in_array($item['difficulty'] ?? '', Book::DIFFICULTIES, true) ? $item['difficulty'] : 'intermediate',
                'reason_for_recommendation' => trim((string) ($item['reason_for_recommendation'] ?? '')),
                'key_concept' => trim((string) ($item['key_concept'] ?? '')),
            ];
        }

        $gap->update([
            'suggested_books' => $suggestions,
        ]);

        return $suggestions;
    }

    /**
     * One-click add a suggested book directly into the Book Library as 'Planned'.
     */
    public function importSuggestedBook(KnowledgeGap $gap, array $suggestedBook): Book
    {
        $teamId = $gap->team_id;

        // Author
        $authorName = trim($suggestedBook['author'] ?? 'Unknown Author');
        $author = Author::firstOrCreate(
            ['team_id' => $teamId, 'name' => $authorName],
            ['user_id' => auth()->id()]
        );

        // Topic
        $topicName = trim($suggestedBook['topic'] ?? 'General Business');
        $topic = Topic::firstOrCreate(
            ['team_id' => $teamId, 'slug' => Str::slug($topicName)],
            ['name' => $topicName, 'user_id' => auth()->id()]
        );

        $title = trim($suggestedBook['title']);
        $slug = Str::slug($title);

        $existingBook = Book::where('team_id', $teamId)->where('slug', $slug)->first();
        if ($existingBook) {
            return $existingBook;
        }

        $book = Book::create([
            'team_id' => $teamId,
            'user_id' => auth()->id(),
            'author_id' => $author->id,
            'main_topic_id' => $topic->id,
            'title' => $title,
            'slug' => $slug,
            'difficulty' => $suggestedBook['difficulty'] ?? 'intermediate',
            'description' => $suggestedBook['reason_for_recommendation'] ?? "Added to address knowledge gap: {$gap->query}",
            'status' => 'active',
        ]);

        // Attach reading progress as planned
        $book->progressRecords()->create([
            'team_id' => $teamId,
            'user_id' => auth()->id(),
            'status' => 'planned',
            'progress_percent' => 0,
            'notes' => "Acquired to resolve team knowledge gap: '{$gap->query}'",
        ]);

        $gap->update([
            'status' => KnowledgeGap::STATUS_RESOLVED,
            'admin_notes' => "Resolved by adding book '{$book->title}' to library.",
        ]);

        return $book;
    }
}
