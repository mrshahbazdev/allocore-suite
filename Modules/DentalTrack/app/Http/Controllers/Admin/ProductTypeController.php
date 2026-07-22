<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Models\Company;
use Modules\DentalTrack\Models\ProductType;

class ProductTypeController extends Controller
{
    public function index(): View
    {
        $productTypes = ProductType::with('company')->orderByDesc('created_at')->paginate(20);

        return view('dentaltrack::admin.product-types.index', compact('productTypes'));
    }

    public function create(): View
    {
        $companies = Company::where('is_active', true)->get();

        return view('dentaltrack::admin.product-types.form', compact('companies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        ProductType::create($validated);

        return redirect()->route('dentaltrack.admin.product-types.index')->with('success', __('Product type created.'));
    }

    public function edit(ProductType $productType): View
    {
        $companies = Company::where('is_active', true)->get();

        return view('dentaltrack::admin.product-types.form', compact('productType', 'companies'));
    }

    public function update(Request $request, ProductType $productType): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $productType->update($validated);

        return redirect()->route('dentaltrack.admin.product-types.index')->with('success', __('Product type updated.'));
    }

    public function destroy(ProductType $productType): RedirectResponse
    {
        $productType->delete();

        return back()->with('success', __('Product type deleted.'));
    }

    private function rules(): array
    {
        return [
            'dentaltrack_company_id' => 'required|exists:dentaltrack_companies,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}
