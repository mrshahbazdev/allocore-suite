<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\BookIntelligence\Jobs\GenerateBookAnalysisJob;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\BookAnalysis;
use Modules\ClusterForge\Services\AiService;

class BookAnalysisController extends Controller
{
    public function store(Request $request, Book $book, AiService $ai): RedirectResponse
    {
        $validated = $request->validate([
            'source_material' => ['nullable', 'string', 'max:60000'],
        ]);

        if (! $ai->isConfigured()) {
            return redirect()
                ->route('bookintelligence.books.show', $book)
                ->with('error', __('AI analysis is unavailable until an administrator configures an AI provider.'));
        }

        $analysis = $book->analysis;

        if ($analysis?->isInProgress()) {
            return redirect()
                ->route('bookintelligence.books.show', $book)
                ->with('error', __('This book is already being analyzed.'));
        }

        $analysis = $book->analysis()->updateOrCreate(
            ['book_id' => $book->id],
            [
                'team_id' => $book->team_id,
                'user_id' => $request->user()->id,
                'status' => BookAnalysis::STATUS_PENDING,
                'source_material' => $validated['source_material'] ?? null,
                'error' => null,
            ],
        );

        GenerateBookAnalysisJob::dispatch($analysis->id);

        return redirect()
            ->route('bookintelligence.books.show', $book)
            ->with('success', __('Book analysis queued. You can keep working while Allocore prepares the intelligence.'));
    }

    public function status(Book $book): JsonResponse
    {
        $analysis = $book->analysis;

        if (! $analysis) {
            return response()->json([
                'status' => null,
                'is_in_progress' => false,
                'is_completed' => false,
            ]);
        }

        return response()->json([
            'status' => $analysis->status,
            'status_label' => $analysis->statusLabel(),
            'is_in_progress' => $analysis->isInProgress(),
            'is_completed' => $analysis->isCompleted(),
            'is_outdated' => $analysis->isOutdated(),
            'has_error' => $analysis->status === BookAnalysis::STATUS_FAILED,
        ]);
    }
}
