<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\ContentOpportunity;
use Modules\BookIntelligence\Models\GeneratedBlog;
use Modules\BookIntelligence\Services\AutomatedBlogGenerator;
use Modules\BookIntelligence\Services\ContentOpportunityDiscoveryService;

class ContentEngineController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type', 'all');
        $status = $request->query('status', 'all');

        $query = ContentOpportunity::query()->with(['book.author', 'post']);

        if ($type !== 'all') {
            $query->where('content_type', $type);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $opportunities = $query->latest()->paginate(12)->withQueryString();
        $generatedBlogs = GeneratedBlog::query()->with(['book', 'post'])->latest()->take(5)->get();
        $books = Book::query()->orderBy('title')->get();

        $stats = [
            'total' => ContentOpportunity::count(),
            'blogs' => ContentOpportunity::where('content_type', 'blog')->count(),
            'faqs' => ContentOpportunity::where('content_type', 'faq')->count(),
            'linkedin' => ContentOpportunity::where('content_type', 'linkedin')->count(),
            'published' => ContentOpportunity::where('status', 'published')->count(),
        ];

        return view('bookintelligence::content.index', compact('opportunities', 'generatedBlogs', 'books', 'stats', 'type', 'status'));
    }

    public function discover(Request $request, ContentOpportunityDiscoveryService $service): RedirectResponse
    {
        @set_time_limit(300);
        $bookId = $request->input('book_id');
        $book = $bookId ? Book::find($bookId) : null;

        try {
            $created = $service->discoverOpportunities($book);

            return redirect()->route('bookintelligence.content.index')
                ->with('status', count($created) . ' new high-traffic SEO and content opportunities discovered!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to discover opportunities: ' . $e->getMessage()]);
        }
    }

    public function generateBlog(Request $request, AutomatedBlogGenerator $generator): RedirectResponse
    {
        @set_time_limit(300);
        $validated = $request->validate([
            'book_id' => ['required', 'exists:bookintelligence_books,id'],
            'opportunity_id' => ['nullable', 'exists:bookintelligence_content_opportunities,id'],
            'custom_topic' => ['nullable', 'string', 'max:255'],
        ]);

        $book = Book::findOrFail($validated['book_id']);
        $opportunity = !empty($validated['opportunity_id']) ? ContentOpportunity::find($validated['opportunity_id']) : null;

        try {
            $blog = $generator->generate($book, $opportunity, $validated['custom_topic'] ?? null);

            return redirect()->route('bookintelligence.content.blog.show', $blog->id)
                ->with('status', '8-Section structured blog article generated successfully!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to generate blog article: ' . $e->getMessage()]);
        }
    }

    public function showBlog(GeneratedBlog $blog): View
    {
        $blog->loadMissing(['book.author', 'opportunity', 'post']);
        $categories = BlogCategory::query()->orderBy('name')->get();

        return view('bookintelligence::content.blog-show', compact('blog', 'categories'));
    }

    public function publishBlog(Request $request, GeneratedBlog $blog, AutomatedBlogGenerator $generator): RedirectResponse
    {
        $categoryId = $request->input('category_id');

        try {
            $post = $generator->publishToCms($blog, $categoryId ? (int) $categoryId : null);

            return redirect()->route('bookintelligence.content.blog.show', $blog->id)
                ->with('status', "Blog article successfully published to Allocore Blog! (Post #{$post->id})");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to publish to CMS: ' . $e->getMessage()]);
        }
    }
}
