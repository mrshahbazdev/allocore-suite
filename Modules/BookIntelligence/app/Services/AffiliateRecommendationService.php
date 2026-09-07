<?php

namespace Modules\BookIntelligence\Services;

use Illuminate\Http\Request;
use Modules\BookIntelligence\Models\AffiliateClick;
use Modules\BookIntelligence\Models\Book;

class AffiliateRecommendationService
{
    /**
     * Generate responsive, styled HTML for the Book Recommendation Box (Section 8 / in-content widget).
     */
    public function renderRecommendationBoxHtml(Book $book, ?string $whyRecommended = null): string
    {
        $bookTitle = e($book->title);
        $authorName = e($book->author?->name ?? 'Author');
        $coverUrl = $book->cover_url ?: asset('images/book-placeholder.png');
        $explanation = e($whyRecommended ?: ($book->analysis?->short_summary ?? $book->description ?? 'Highly recommended reading for operational excellence.'));
        $trackUrl = route('bookintelligence.affiliate.redirect', ['book' => $book->id, 'source' => 'blog']);

        return <<<HTML
<div class="allocore-book-recommendation-box my-10 p-6 sm:p-8 rounded-3xl border-2 border-amber-200 bg-gradient-to-br from-amber-50/70 via-orange-50/40 to-white shadow-sm flex flex-col sm:flex-row items-center sm:items-start gap-6">
    <div class="flex-shrink-0 w-28 h-40 rounded-xl overflow-hidden shadow-lg border border-slate-200 bg-white flex items-center justify-center">
        <img src="{$coverUrl}" alt="{$bookTitle}" class="w-full h-full object-cover" onerror="this.src='https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=300&q=80'">
    </div>
    <div class="flex-1 text-center sm:text-left space-y-3">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold uppercase tracking-wider">
            <span>⭐</span> Recommended Reading
        </div>
        <h3 class="text-xl font-extrabold text-slate-900">{$bookTitle}</h3>
        <p class="text-sm font-medium text-slate-500">By {$authorName}</p>
        <p class="text-sm leading-relaxed text-slate-700">{$explanation}</p>
        <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-3">
            <a href="{$trackUrl}" target="_blank" rel="noopener noreferrer sponsored" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 text-white font-bold text-sm shadow-md hover:from-orange-600 hover:to-orange-700 transition">
                <span>🛒</span> Get This Book on Amazon ↗
            </a>
        </div>
    </div>
</div>
HTML;
    }

    /**
     * Track an affiliate link click and return the target URL.
     */
    public function trackClick(Book $book, Request $request, string $sourceType = 'direct', ?int $sourceId = null): string
    {
        AffiliateClick::create([
            'team_id' => $book->team_id,
            'book_id' => $book->id,
            'user_id' => auth()->id(),
            'referrer_url' => $request->headers->get('referer'),
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_converted' => false,
            'commission_amount' => 0.00,
        ]);

        return $book->affiliate_link ?: route('bookintelligence.books.show', $book->id);
    }

    /**
     * Get aggregate affiliate monetization analytics.
     *
     * @return array<string, mixed>
     */
    public function getPerformanceStats(): array
    {
        $totalClicks = AffiliateClick::count();
        $totalConversions = AffiliateClick::where('is_converted', true)->count();
        $totalRevenue = (float) AffiliateClick::sum('commission_amount');
        $conversionRate = $totalClicks > 0 ? round(($totalConversions / $totalClicks) * 100, 1) : 0.0;

        $topBooks = Book::query()
            ->withCount('progressRecords')
            ->with(['author'])
            ->get()
            ->map(function ($b) {
                $clicks = AffiliateClick::where('book_id', $b->id)->count();
                $rev = (float) AffiliateClick::where('book_id', $b->id)->sum('commission_amount');
                return [
                    'book' => $b,
                    'clicks' => $clicks,
                    'revenue' => $rev,
                ];
            })
            ->sortByDesc('clicks')
            ->take(5)
            ->values();

        return [
            'total_clicks' => $totalClicks,
            'total_conversions' => $totalConversions,
            'conversion_rate' => $conversionRate,
            'total_revenue' => $totalRevenue,
            'top_books' => $topBooks,
        ];
    }
}
