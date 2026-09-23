<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\BookIntelligence\Models\AffiliateClick;
use Modules\BookIntelligence\Models\AssessmentSubmission;
use Modules\BookIntelligence\Models\Author;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\BookAnalysis;
use Modules\BookIntelligence\Models\ChallengeSubmission;
use Modules\BookIntelligence\Models\CompetencyRole;
use Modules\BookIntelligence\Models\ContentOpportunity;
use Modules\BookIntelligence\Models\GeneratedBlog;
use Modules\BookIntelligence\Models\KnowledgeGap;
use Modules\BookIntelligence\Models\LearningPath;
use Modules\BookIntelligence\Models\QuestionMapping;
use Modules\BookIntelligence\Models\ReadingProgress;
use Modules\BookIntelligence\Models\SearchQuery;
use Modules\BookIntelligence\Models\Topic;
use Modules\BookIntelligence\Models\UserExpertiseProfile;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // 1. Knowledge Metrics
        $totalBooks = Book::count();
        $totalAnalyzed = BookAnalysis::where('status', BookAnalysis::STATUS_COMPLETED)->count();
        $totalFaqs = QuestionMapping::count();
        $openGaps = KnowledgeGap::whereIn('status', ['open', 'reviewing'])->count();
        $recentSearches = SearchQuery::latest()->take(5)->get();

        // 2. Employee Learning & Expertise Metrics
        $activeLearningPaths = LearningPath::where('status', 'active')->count();
        $assessmentsPassed = AssessmentSubmission::where('passed', true)->count();
        $challengesCompleted = ChallengeSubmission::where('status', 'graded')->count();
        $teamProfiles = UserExpertiseProfile::with(['user', 'currentRole', 'targetRole'])->orderByDesc('points')->take(5)->get();
        $totalRoles = CompetencyRole::count();

        // 3. Content & SEO Metrics
        $seoOpportunities = ContentOpportunity::count();
        $generatedBlogs = GeneratedBlog::count();
        $publishedPosts = GeneratedBlog::where('status', 'published')->count();

        // 4. Affiliate Monetization Metrics
        $totalAffiliateClicks = AffiliateClick::count();
        $totalAffiliateRevenue = (float) AffiliateClick::sum('commission_amount');
        $conversionRate = $totalAffiliateClicks > 0
            ? round((AffiliateClick::where('is_converted', true)->count() / $totalAffiliateClicks) * 100, 1)
            : 0.0;

        $recentBooks = Book::with(['author', 'mainTopic', 'analysis'])
            ->latest()
            ->take(4)
            ->get();

        $stats = [
            'books' => $totalBooks,
            'analyzed' => $totalAnalyzed,
            'faqs' => $totalFaqs,
            'open_gaps' => $openGaps,
            'learning_paths' => $activeLearningPaths,
            'assessments_passed' => $assessmentsPassed,
            'challenges_completed' => $challengesCompleted,
            'roles' => $totalRoles,
            'seo_opportunities' => $seoOpportunities,
            'generated_blogs' => $generatedBlogs,
            'published_blogs' => $publishedPosts,
            'affiliate_clicks' => $totalAffiliateClicks,
            'affiliate_revenue' => $totalAffiliateRevenue,
            'conversion_rate' => $conversionRate,
        ];

        return view('bookintelligence::dashboard.index', compact('stats', 'recentBooks', 'recentSearches', 'teamProfiles'));
    }
}
