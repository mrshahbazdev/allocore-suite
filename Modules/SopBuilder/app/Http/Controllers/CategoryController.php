<?php

namespace Modules\SopBuilder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\SopBuilder\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        return view('sopbuilder::categories.index', compact('categories'));
    }

    public function create()
    {
        return view('sopbuilder::categories.form', ['category' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        Category::create($data);

        return redirect()->route('sopbuilder.categories.index')->with('message', __('Category created.'));
    }

    public function edit(Category $category)
    {
        return view('sopbuilder::categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $category->update($data);

        return redirect()->route('sopbuilder.categories.index')->with('message', __('Category updated.'));
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('sopbuilder.categories.index')->with('message', __('Category deleted.'));
    }
}
