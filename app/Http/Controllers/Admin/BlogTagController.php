<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogTagController extends Controller
{
    public function index()
    {
        $tags = BlogTag::orderBy('name')->get();

        return view('admin.blog.tags.index', compact('tags'));
    }

    public function show(BlogTag $tag)
    {
        return redirect()->route('admin.blog.tags.edit', $tag);
    }

    public function create()
    {
        return view('admin.blog.tags.form', ['tag' => new BlogTag]);
    }

    public function store(Request $request)
    {
        $this->saveTag(new BlogTag, $this->validateTag($request));

        return redirect()->route('admin.blog.tags.index')->with('success', __('Tag created.'));
    }

    public function edit(BlogTag $tag)
    {
        return view('admin.blog.tags.form', compact('tag'));
    }

    public function update(Request $request, BlogTag $tag)
    {
        $this->saveTag($tag, $this->validateTag($request, $tag));

        return redirect()->route('admin.blog.tags.index')->with('success', __('Tag updated.'));
    }

    public function destroy(BlogTag $tag)
    {
        $tag->delete();

        return redirect()->route('admin.blog.tags.index')->with('success', __('Tag deleted.'));
    }

    protected function validateTag(Request $request, ?BlogTag $tag = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('blog_tags', 'slug')->ignore($tag?->id)],
        ]);
    }

    protected function saveTag(BlogTag $tag, array $validated): BlogTag
    {
        $validated['slug'] = Str::slug($validated['slug']);
        $tag->fill($validated);
        $tag->save();

        return $tag;
    }
}
