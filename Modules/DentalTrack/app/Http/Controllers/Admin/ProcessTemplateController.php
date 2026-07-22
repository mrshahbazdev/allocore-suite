<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Models\ProcessTemplate;
use Modules\DentalTrack\Models\ProductType;

class ProcessTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProcessTemplate::with('productType')->orderBy('sort_order');

        if ($request->product_type_id) {
            $query->where('dentaltrack_product_type_id', $request->product_type_id);
        }

        $templates = $query->paginate(50)->withQueryString();
        $productTypes = ProductType::where('is_active', true)->get();

        return view('dentaltrack::admin.process-templates.index', compact('templates', 'productTypes'));
    }

    public function create(): View
    {
        $productTypes = ProductType::where('is_active', true)->get();

        return view('dentaltrack::admin.process-templates.form', compact('productTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        ProcessTemplate::create($validated);

        return redirect()->route('dentaltrack.admin.process-templates.index')->with('success', __('Template step created.'));
    }

    public function edit(ProcessTemplate $processTemplate): View
    {
        $productTypes = ProductType::where('is_active', true)->get();

        return view('dentaltrack::admin.process-templates.form', compact('processTemplate', 'productTypes'));
    }

    public function update(Request $request, ProcessTemplate $processTemplate): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $processTemplate->update($validated);

        return redirect()->route('dentaltrack.admin.process-templates.index')->with('success', __('Template step updated.'));
    }

    public function destroy(ProcessTemplate $processTemplate): RedirectResponse
    {
        $processTemplate->delete();

        return back()->with('success', __('Template step deleted.'));
    }

    private function rules(): array
    {
        return [
            'dentaltrack_product_type_id' => 'required|exists:dentaltrack_product_types,id',
            'sort_order' => 'required|integer|min:1',
            'step_name' => 'required|string|max:255',
            'expected_minutes' => 'nullable|integer|min:0',
        ];
    }
}
