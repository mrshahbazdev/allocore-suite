<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class IndustryController extends Controller
{
    public function index(Request $request)
    {
        $clusters = Industry::clusters()
            ->with('children')
            ->get();

        $subIndustries = Industry::subIndustries()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%');
            })
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.industries.index', compact('clusters', 'subIndustries'));
    }

    public function create()
    {
        $clusters = Industry::clusters()->get();

        return view('admin.industries.create', compact('clusters'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        Industry::create($validated);

        return redirect()->route('admin.industries.index')->with('success', __('Industry created.'));
    }

    public function edit(Industry $industry)
    {
        $clusters = Industry::clusters()->get();

        return view('admin.industries.edit', compact('industry', 'clusters'));
    }

    public function update(Request $request, Industry $industry)
    {
        $validated = $this->validateData($request, $industry);

        $industry->update($validated);

        return redirect()->route('admin.industries.index')->with('success', __('Industry updated.'));
    }

    public function destroy(Industry $industry)
    {
        $industry->children()->update(['parent_id' => null, 'is_active' => false]);
        $industry->delete();

        return redirect()->route('admin.industries.index')->with('success', __('Industry deleted.'));
    }

    private function validateData(Request $request, ?Industry $industry = null): array
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists('industries', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('industries', 'slug')->ignore($industry?->id)],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['locale'] = app()->getLocale();

        if ($validated['parent_id'] === '') {
            $validated['parent_id'] = null;
        }

        return $validated;
    }
}
