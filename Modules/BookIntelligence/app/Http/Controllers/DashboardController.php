<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\BookIntelligence\Models\Author;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\ReadingProgress;
use Modules\BookIntelligence\Models\Topic;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $books = Book::with(['author', 'mainTopic', 'currentUserProgress'])
            ->latest()
            ->limit(6)
            ->get();

        $readingCounts = ReadingProgress::where('user_id', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'books' => Book::count(),
            'authors' => Author::count(),
            'topics' => Topic::count(),
            'read' => (int) $readingCounts->get('read', 0),
        ];

        $setup = [
            [
                'label' => __('Create your library structure'),
                'description' => __('Add authors, publishers, and topics so every book stays organized.'),
                'complete' => $stats['authors'] > 0 && $stats['topics'] > 0,
                'route' => route('bookintelligence.setup.index'),
                'action' => __('Open library setup'),
            ],
            [
                'label' => __('Add your first book'),
                'description' => __('Capture metadata, difficulty, job-role relevance, and affiliate details.'),
                'complete' => $stats['books'] > 0,
                'route' => route('bookintelligence.books.create'),
                'action' => __('Add a book'),
            ],
            [
                'label' => __('Start a reading plan'),
                'description' => __('Choose Planned, Currently Reading, or Read and track progress.'),
                'complete' => $readingCounts->sum() > 0,
                'route' => route('bookintelligence.books.index'),
                'action' => __('Choose a book'),
            ],
        ];

        return view('bookintelligence::dashboard.index', compact('books', 'readingCounts', 'setup', 'stats'));
    }
}
