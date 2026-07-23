<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogTag;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::published()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($request->category, function ($query, $slug) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
            })
            ->when($request->tag, function ($query, $slug) {
                $query->whereHas('tags', fn ($q) => $q->where('slug', $slug));
            })
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->paginate(12);

        $featured = Post::published()->featured()->latest('published_at')->take(3)->get();
        $categories = BlogCategory::active()->orderBy('sort_order')->get();
        $tags = BlogTag::orderBy('name')->take(30)->get();

        return view('blog.index', compact('posts', 'featured', 'categories', 'tags'));
    }

    public function show(Post $post): View
    {
        abort_if(! $post->isPublished(), 404);

        $post->increment('views');

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->where(function ($query) use ($post) {
                $query->where('category_id', $post->category_id)
                    ->orWhereHas('tags', function ($q) use ($post) {
                        $q->whereIn('blog_tags.id', $post->tags->pluck('id'));
                    });
            })
            ->latest('published_at')
            ->take(3)
            ->get();

        $comments = $post->approvedComments()->whereNull('parent_id')->with('approvedReplies')->latest()->get();

        return view('blog.show', compact('post', 'related', 'comments'));
    }

    public function category(BlogCategory $category)
    {
        $posts = Post::published()->where('category_id', $category->id)->latest('published_at')->paginate(12);

        return view('blog.category', compact('category', 'posts'));
    }

    public function tag(BlogTag $tag)
    {
        $posts = $tag->posts()->published()->latest('published_at')->paginate(12);

        return view('blog.tag', compact('tag', 'posts'));
    }

    public function feed()
    {
        $posts = Post::published()->latest('published_at')->take(20)->get();

        $xml = view('blog.feed', compact('posts'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function storeComment(Request $request, Post $post)
    {
        abort_if(! $post->isPublished(), 404);

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:blog_comments,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'body' => 'required|string|max:5000',
        ]);

        $validated['ip_address'] = $request->ip();

        BlogComment::create([
            'post_id' => $post->id,
            ...$validated,
        ]);

        return back()->with('success', __('Comment submitted for moderation.'));
    }
}
