<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogCategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::orderBy('sort_order')->get();

        return view('admin.blog.categories.index', compact('categories'));
    }

    public function show(BlogCategory $category)
    {
        return redirect()->route('admin.blog.categories.edit', $category);
    }

    public function create()
    {
        return view('admin.blog.categories.form', ['category' => new BlogCategory]);
    }

    public function store(Request $request)
    {
        $category = $this->saveCategory(new BlogCategory, $this->validateCategory($request));

        return redirect()->route('admin.blog.categories.index')->with('success', __('Category created.'));
    }

    public function edit(BlogCategory $category)
    {
        return view('admin.blog.categories.form', compact('category'));
    }

    public function update(Request $request, BlogCategory $category)
    {
        $this->saveCategory($category, $this->validateCategory($request, $category));

        return redirect()->route('admin.blog.categories.index')->with('success', __('Category updated.'));
    }

    public function destroy(BlogCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.blog.categories.index')->with('success', __('Category deleted.'));
    }

    protected function validateCategory(Request $request, ?BlogCategory $category = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('blog_categories', 'slug')->ignore($category?->id)],
            'description' => 'nullable|string|max:2000',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);
    }

    protected function saveCategory(BlogCategory $category, array $validated): BlogCategory
    {
        $validated['slug'] = Str::slug($validated['slug']);
        $category->fill($validated);
        $category->save();

        return $category;
    }
}
