<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\RepurposedBundle;
use Modules\BookIntelligence\Services\BookRepurposingEngine;

class RepurposingController extends Controller
{
    public function index(): View
    {
        $books = Book::query()->with(['author', 'mainTopic'])->orderBy('title')->get();
        $bundles = RepurposedBundle::query()->with(['book.author'])->latest()->paginate(10);

        return view('bookintelligence::repurposing.index', compact('books', 'bundles'));
    }

    public function show(Book $book): View
    {
        $book->loadMissing(['author', 'mainTopic', 'analysis']);
        $bundle = RepurposedBundle::where('book_id', $book->id)->first();

        return view('bookintelligence::repurposing.show', compact('book', 'bundle'));
    }

    public function generate(Book $book, BookRepurposingEngine $engine): RedirectResponse
    {
        @set_time_limit(300);
        try {
            $bundle = $engine->generateBundle($book);

            return redirect()->route('bookintelligence.repurposing.show', $book->id)
                ->with('status', "Multi-channel content bundle successfully generated for '{$book->title}'!");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to generate repurposed content: ' . $e->getMessage()]);
        }
    }
}
