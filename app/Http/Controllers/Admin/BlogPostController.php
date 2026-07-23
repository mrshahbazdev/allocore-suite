<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['category', 'user'])->latest()->paginate(25);

        return view('admin.blog.posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        return redirect()->route('admin.blog.posts.edit', $post);
    }

    public function create()
    {
        $categories = BlogCategory::active()->orderBy('sort_order')->get();
        $tags = BlogTag::orderBy('name')->get();
        $post = new Post;

        return view('admin.blog.posts.form', compact('post', 'categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePost($request);
        $post = $this->savePost(new Post, $validated, $request);

        return redirect()->route('admin.blog.posts.index')->with('success', __('Post created.'));
    }

    public function edit(Post $post)
    {
        $post->load('tags');
        $categories = BlogCategory::active()->orderBy('sort_order')->get();
        $tags = BlogTag::orderBy('name')->get();

        return view('admin.blog.posts.form', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $this->validatePost($request, $post);
        $this->savePost($post, $validated, $request);

        return redirect()->route('admin.blog.posts.index')->with('success', __('Post updated.'));
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.blog.posts.index')->with('success', __('Post deleted.'));
    }

    protected function validatePost(Request $request, ?Post $post = null): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($post?->id)],
            'category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string|max:2000',
            'body' => 'required|string|max:50000',
            'featured_image' => 'nullable|string|max:1000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:1000',
            'og_image' => 'nullable|string|max:1000',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
        ];

        return $request->validate($rules);
    }

    protected function savePost(Post $post, array $validated, Request $request): Post
    {
        $validated['slug'] = Str::slug($validated['slug']);
        $validated['user_id'] = $request->user()->id;

        if ($request->boolean('is_published') && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        } elseif (! $request->boolean('is_published')) {
            $validated['published_at'] = null;
        }

        $post->fill($validated);
        $post->save();

        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        } else {
            $post->tags()->detach();
        }

        return $post;
    }
}
