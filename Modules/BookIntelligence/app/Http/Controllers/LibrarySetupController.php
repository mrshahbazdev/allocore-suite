<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\BookIntelligence\Models\Author;
use Modules\BookIntelligence\Models\Publisher;
use Modules\BookIntelligence\Models\Topic;

class LibrarySetupController extends Controller
{
    public function index()
    {
        $authors = Author::withCount('books')->orderBy('name')->get();
        $publishers = Publisher::withCount('books')->orderBy('name')->get();
        $topics = Topic::with(['parent', 'children'])->withCount(['mainBooks', 'books'])->orderBy('name')->get();

        return view('bookintelligence::setup.index', compact('authors', 'publishers', 'topics'));
    }

    public function storeAuthor(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('bookintelligence_authors')->where('team_id', $teamId)],
            'website' => ['nullable', 'url', 'max:2048'],
            'bio' => ['nullable', 'string'],
        ]);

        Author::create($validated);

        return back()->with('success', __('Author added.'));
    }

    public function destroyAuthor(Author $author)
    {
        if ($author->books()->exists()) {
            return back()->with('warning', __('This author is used by books and cannot be deleted.'));
        }

        $author->delete();

        return back()->with('success', __('Author deleted.'));
    }

    public function storePublisher(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('bookintelligence_publishers')->where('team_id', $teamId)],
            'website' => ['nullable', 'url', 'max:2048'],
        ]);

        Publisher::create($validated);

        return back()->with('success', __('Publisher added.'));
    }

    public function destroyPublisher(Publisher $publisher)
    {
        if ($publisher->books()->exists()) {
            return back()->with('warning', __('This publisher is used by books and cannot be deleted.'));
        }

        $publisher->delete();

        return back()->with('success', __('Publisher deleted.'));
    }

    public function storeTopic(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', Rule::exists('bookintelligence_topics', 'id')->where('team_id', $teamId)],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = $this->uniqueTopicSlug($validated['name']);
        Topic::create($validated);

        return back()->with('success', __('Topic added.'));
    }

    public function destroyTopic(Topic $topic)
    {
        if ($topic->children()->exists() || $topic->mainBooks()->exists() || $topic->books()->exists()) {
            return back()->with('warning', __('This topic is in use and cannot be deleted.'));
        }

        $topic->delete();

        return back()->with('success', __('Topic deleted.'));
    }

    private function uniqueTopicSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'topic';
        $slug = $base;
        $suffix = 2;

        while (Topic::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
