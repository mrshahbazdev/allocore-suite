<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\AffiliateClick;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Services\AffiliateRecommendationService;

class AffiliateController extends Controller
{
    public function index(AffiliateRecommendationService $service): View
    {
        $stats = $service->getPerformanceStats();
        $recentClicks = AffiliateClick::query()->with(['book', 'user'])->latest()->paginate(15);
        $booksWithAffiliate = Book::query()->whereNotNull('affiliate_link')->with('author')->get();

        return view('bookintelligence::affiliate.index', compact('stats', 'recentClicks', 'booksWithAffiliate'));
    }

    public function redirect(Request $request, Book $book, AffiliateRecommendationService $service): RedirectResponse
    {
        $sourceType = $request->query('source', 'direct');
        $sourceId = $request->query('source_id') ? (int) $request->query('source_id') : null;

        $targetUrl = $service->trackClick($book, $request, $sourceType, $sourceId);

        return redirect()->away($targetUrl);
    }
}
