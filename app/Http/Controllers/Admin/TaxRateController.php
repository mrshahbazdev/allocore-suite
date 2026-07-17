<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function index(Request $request)
    {
        $taxRates = TaxRate::when($request->filled('search'), function ($query) use ($request) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('country', 'like', '%'.$request->search.'%');
        })->latest()->paginate(20)->withQueryString();

        return view('admin.tax-rates.index', compact('taxRates'));
    }

    public function create()
    {
        return view('admin.tax-rates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        TaxRate::create($validated);

        return redirect()->route('admin.tax-rates.index')->with('success', __('admin.tax_rates.created'));
    }

    public function edit(TaxRate $taxRate)
    {
        return view('admin.tax-rates.edit', compact('taxRate'));
    }

    public function update(Request $request, TaxRate $taxRate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        $taxRate->update($validated);

        return redirect()->route('admin.tax-rates.index')->with('success', __('admin.tax_rates.updated'));
    }

    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();

        return redirect()->route('admin.tax-rates.index')->with('success', __('admin.tax_rates.deleted'));
    }
}
