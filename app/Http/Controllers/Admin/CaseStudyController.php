<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CaseStudyController extends Controller
{
    public function index(Request $request)
    {
        $caseStudies = CaseStudy::when($request->filled('search'), function ($query) use ($request) {
            $query->where('title', 'like', '%'.$request->search.'%')
                ->orWhere('company', 'like', '%'.$request->search.'%');
        })
            ->orderBy('sort_order')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.case-studies.index', compact('caseStudies'));
    }

    public function create()
    {
        return view('admin.case-studies.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['title']);

        CaseStudy::create($validated);

        return redirect()->route('admin.case-studies.index')->with('success', __('Case study created.'));
    }

    public function edit(CaseStudy $caseStudy)
    {
        return view('admin.case-studies.edit', compact('caseStudy'));
    }

    public function update(Request $request, CaseStudy $caseStudy)
    {
        $validated = $this->validateData($request);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['title']);

        $caseStudy->update($validated);

        return redirect()->route('admin.case-studies.index')->with('success', __('Case study updated.'));
    }

    public function destroy(CaseStudy $caseStudy)
    {
        $caseStudy->delete();

        return redirect()->route('admin.case-studies.index')->with('success', __('Case study deleted.'));
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:255',
            'challenge' => 'nullable|string|max:5000',
            'solution' => 'nullable|string|max:5000',
            'result' => 'nullable|string|max:5000',
            'metrics' => 'nullable|string|max:2000',
            'image' => 'nullable|string|max:1000',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if (! empty($validated['metrics'])) {
            $lines = array_filter(array_map('trim', explode("\n", $validated['metrics'])));
            $metrics = [];
            foreach ($lines as $line) {
                if (str_contains($line, ':')) {
                    [$key, $value] = explode(':', $line, 2);
                    $metrics[trim($key)] = trim($value);
                }
            }
            $validated['metrics'] = $metrics;
        } else {
            $validated['metrics'] = [];
        }

        return $validated;
    }
}
