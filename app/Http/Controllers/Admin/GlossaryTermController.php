<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlossaryTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GlossaryTermController extends Controller
{
    public function index(Request $request)
    {
        $terms = GlossaryTerm::when($request->filled('search'), function ($query) use ($request) {
            $query->where('term', 'like', '%'.$request->search.'%')
                ->orWhere('definition', 'like', '%'.$request->search.'%');
        })
            ->orderBy('sort_order')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.glossary.index', compact('terms'));
    }

    public function create()
    {
        return view('admin.glossary.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        GlossaryTerm::create($validated);

        return redirect()->route('admin.glossary.index')->with('success', __('Glossary term created.'));
    }

    public function edit(GlossaryTerm $glossary)
    {
        return view('admin.glossary.edit', compact('glossary'));
    }

    public function update(Request $request, GlossaryTerm $glossary)
    {
        $validated = $this->validateData($request);

        $glossary->update($validated);

        return redirect()->route('admin.glossary.index')->with('success', __('Glossary term updated.'));
    }

    public function destroy(GlossaryTerm $glossary)
    {
        $glossary->delete();

        return redirect()->route('admin.glossary.index')->with('success', __('Glossary term deleted.'));
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'term' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'definition' => 'required|string|max:10000',
            'category' => 'nullable|string|max:100',
            'related_modules' => 'nullable|string|max:1000',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['term']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if (! empty($validated['related_modules'])) {
            $validated['related_modules'] = array_values(array_filter(array_map('trim', explode(',', $validated['related_modules']))));
        } else {
            $validated['related_modules'] = [];
        }

        return $validated;
    }
}
