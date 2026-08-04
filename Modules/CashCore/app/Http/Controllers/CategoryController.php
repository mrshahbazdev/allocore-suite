<?php

namespace Modules\CashCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\CashCore\Models\CashCategory;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = CashCategory::orderBy('type')->orderBy('name')->get();

        return view('cashcore::categories.index', compact('categories'));
    }

    public function show(CashCategory $category): RedirectResponse
    {
        return redirect()->route('cashcore.categories.edit', $category);
    }

    public function create(): View
    {
        return view('cashcore::categories.form', ['category' => new CashCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        CashCategory::create($validated);

        return redirect()->route('cashcore.categories.index')->with('success', __('Category created.'));
    }

    public function edit(CashCategory $category): View
    {
        return view('cashcore::categories.form', compact('category'));
    }

    public function update(Request $request, CashCategory $category): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $category->update($validated);

        return redirect()->route('cashcore.categories.index')->with('success', __('Category updated.'));
    }

    public function destroy(CashCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('cashcore.categories.index')->with('success', __('Category deleted.'));
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
        ];
    }
}
