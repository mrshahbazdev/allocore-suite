<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\KnowledgeGap;
use Modules\BookIntelligence\Services\KnowledgeGapService;

class KnowledgeGapController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $query = KnowledgeGap::query()->with('user');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $gaps = $query->orderByDesc('search_count')->latest()->paginate(12)->withQueryString();

        $stats = [
            'total' => KnowledgeGap::count(),
            'open' => KnowledgeGap::where('status', KnowledgeGap::STATUS_OPEN)->count(),
            'reviewing' => KnowledgeGap::where('status', KnowledgeGap::STATUS_REVIEWING)->count(),
            'resolved' => KnowledgeGap::where('status', KnowledgeGap::STATUS_RESOLVED)->count(),
        ];

        return view('bookintelligence::gaps.index', compact('gaps', 'stats', 'status'));
    }

    public function refreshRecommendations(KnowledgeGap $gap, KnowledgeGapService $service): RedirectResponse
    {
        try {
            $service->generateRecommendationsForGap($gap);

            return redirect()->route('bookintelligence.gaps.index')
                ->with('status', "AI recommendations refreshed for gap: '{$gap->query}'.");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to generate recommendations: ' . $e->getMessage()]);
        }
    }

    public function importBook(Request $request, KnowledgeGap $gap, KnowledgeGapService $service): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'author' => ['required', 'string'],
            'topic' => ['nullable', 'string'],
            'difficulty' => ['nullable', 'string'],
            'reason_for_recommendation' => ['nullable', 'string'],
        ]);

        try {
            $book = $service->importSuggestedBook($gap, $validated);

            return redirect()->route('bookintelligence.books.show', $book->id)
                ->with('status', "Book '{$book->title}' has been added to the library under Planned Reading.");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to import book: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, KnowledgeGap $gap): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,reviewing,resolved,dismissed'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $gap->update($validated);

        return redirect()->route('bookintelligence.gaps.index')
            ->with('status', 'Knowledge gap status updated.');
    }
}
