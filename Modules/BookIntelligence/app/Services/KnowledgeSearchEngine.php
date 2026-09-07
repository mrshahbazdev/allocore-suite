<?php

namespace Modules\BookIntelligence\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\KnowledgeGap;
use Modules\BookIntelligence\Models\QuestionMapping;
use Modules\BookIntelligence\Models\SearchQuery;
use Modules\ClusterForge\Services\AiService;
use Throwable;

class KnowledgeSearchEngine
{
    public function __construct(
        private readonly AiService $ai,
        private readonly KnowledgeGapService $gapService,
    ) {}

    /**
     * Search organizational knowledge across all books and synthesizes an intelligent answer.
     *
     * @return array{
     *     query: string,
     *     has_results: bool,
     *     answer: ?string,
     *     primary_book: ?Book,
     *     companion_books: Collection<int, Book>,
     *     matched_mappings: Collection<int, QuestionMapping>,
     *     confidence: string
     * }
     */
    public function search(string $query, ?User $user = null): array
    {
        $query = trim($query);
        if ($query === '') {
            return [
                'query' => '',
                'has_results' => false,
                'answer' => null,
                'primary_book' => null,
                'companion_books' => collect(),
                'matched_mappings' => collect(),
                'confidence' => 'none',
            ];
        }

        // 1. Search Question Mappings
        $matchedMappings = QuestionMapping::query()
            ->active()
            ->search($query)
            ->with(['book.author', 'book.mainTopic', 'book.analysis'])
            ->take(6)
            ->get();

        // 2. Search Books & Analyses
        $matchedBooks = Book::query()
            ->search($query)
            ->with(['author', 'mainTopic', 'analysis'])
            ->take(6)
            ->get();

        // Combine unique books
        $allBookIds = $matchedMappings->pluck('book_id')
            ->merge($matchedBooks->pluck('id'))
            ->unique()
            ->filter();

        $books = Book::query()
            ->whereIn('id', $allBookIds)
            ->with(['author', 'mainTopic', 'analysis'])
            ->get();

        // If no matching books or questions found in the library
        if ($books->isEmpty()) {
            // Record Knowledge Gap
            $this->gapService->recordGap($query, $user);

            // Log search query
            SearchQuery::create([
                'team_id' => $user?->currentTeam?->id ?? auth()->user()?->currentTeam?->id ?? 1,
                'user_id' => $user?->id ?? auth()->id(),
                'query' => $query,
                'ai_answer' => null,
                'matched_book_id' => null,
                'matched_book_ids' => [],
                'has_results' => false,
                'is_resolved' => false,
            ]);

            return [
                'query' => $query,
                'has_results' => false,
                'answer' => null,
                'primary_book' => null,
                'companion_books' => collect(),
                'matched_mappings' => collect(),
                'confidence' => 'gap_detected',
            ];
        }

        $primaryBook = $books->first();
        $companionBooks = $books->slice(1)->values();

        // Synthesize AI answer from matched context
        $aiAnswer = $this->synthesizeAnswer($query, $primaryBook, $matchedMappings, $companionBooks);

        // Log search query
        SearchQuery::create([
            'team_id' => $primaryBook->team_id,
            'user_id' => $user?->id ?? auth()->id(),
            'query' => $query,
            'ai_answer' => $aiAnswer,
            'matched_book_id' => $primaryBook->id,
            'matched_book_ids' => $books->pluck('id')->all(),
            'has_results' => true,
            'is_resolved' => true,
        ]);

        return [
            'query' => $query,
            'has_results' => true,
            'answer' => $aiAnswer,
            'primary_book' => $primaryBook,
            'companion_books' => $companionBooks,
            'matched_mappings' => $matchedMappings,
            'confidence' => 'high',
        ];
    }

    private function synthesizeAnswer(
        string $query,
        Book $primaryBook,
        Collection $mappings,
        Collection $companionBooks
    ): string {
        try {
            $context = [
                'query' => $query,
                'primary_book' => [
                    'title' => $primaryBook->title,
                    'author' => $primaryBook->author?->name,
                    'short_summary' => $primaryBook->analysis?->short_summary,
                    'key_takeaways' => $primaryBook->analysis?->key_takeaways,
                    'frameworks' => $primaryBook->analysis?->frameworks,
                    'recommendations' => $primaryBook->analysis?->actionable_recommendations,
                ],
                'matched_faqs' => $mappings->map(fn ($m) => [
                    'question' => $m->question,
                    'answer' => $m->answer_excerpt,
                    'problem' => $m->problem_statement,
                    'trigger' => $m->when_to_read_trigger,
                ])->values()->all(),
                'companion_books' => $companionBooks->map(fn ($b) => "{$b->title} by " . ($b->author?->name ?? 'Unknown'))->values()->all(),
            ];

            $prompt = <<<PROMPT
You are Allocore's Executive Knowledge Advisor. Answer the user's natural language question accurately and directly using the organizational knowledge extracted from our book library.

User Question: "{$query}"

Source Context:
PROMPT
            . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            . "\n\nProvide a clear, authoritative, and actionable 2-3 paragraph answer citing insights from '{$primaryBook->title}'. Explain the practical methodology and recommend next steps.";

            $response = $this->ai->generateText($prompt, 0.2);

            return trim($response);
        } catch (Throwable) {
            // Fallback answer if AI service fails
            $fallback = $mappings->first()?->answer_excerpt
                ?: ($primaryBook->analysis?->short_summary ?? $primaryBook->description);

            return "Based on '{$primaryBook->title}' by " . ($primaryBook->author?->name ?? 'Author') . ":\n\n" . $fallback;
        }
    }
}
