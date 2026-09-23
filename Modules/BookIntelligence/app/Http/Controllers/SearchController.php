<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\QuestionMapping;
use Modules\BookIntelligence\Models\SearchQuery;
use Modules\BookIntelligence\Services\KnowledgeSearchEngine;

class SearchController extends Controller
{
    public function index(Request $request, KnowledgeSearchEngine $engine): View
    {
        $query = $request->query('q');
        $searchResult = null;

        if (filled($query)) {
            $searchResult = $engine->search($query, auth()->user());
        }

        $recentSearches = SearchQuery::query()
            ->latest()
            ->take(6)
            ->get();

        $sampleQuestions = [
            'How can I reduce DSO and accelerate cash collection?',
            'How do I scale an outbound sales organization past 10 reps?',
            'What is the optimal cadence for executive 1-on-1 meetings?',
            'How do I build a predictable enterprise pipeline with cold outbound?',
            'How do I calculate unit economics and customer lifetime value (LTV)?',
        ];

        return view('bookintelligence::search.index', compact('query', 'searchResult', 'recentSearches', 'sampleQuestions'));
    }
}
