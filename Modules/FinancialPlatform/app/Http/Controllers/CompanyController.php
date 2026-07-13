<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialPlatform\Models\Company;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->withCount('analyses')
            ->latest()
            ->paginate(12);

        return view('financialplatform::companies.index', compact('companies'));
    }

    public function create()
    {
        return view('financialplatform::companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        Company::create(array_merge(
            $request->only(['name', 'industry', 'currency', 'country', 'description']),
            ['user_id' => auth()->id()]
        ));

        return redirect()->route('companies.index')
            ->with('success', 'Unternehmen erfolgreich angelegt.');
    }

    public function show(Company $company)
    {
        $analyses = $company->analyses()->with('kpiResults')->latest()->get();

        return view('financialplatform::companies.show', compact('company', 'analyses'));
    }

    public function edit(Company $company)
    {
        return view('financialplatform::companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
        ]);

        $company->update($request->only(['name', 'industry', 'currency', 'country', 'description']));

        return redirect()->route('companies.show', $company)
            ->with('success', 'Unternehmen aktualisiert.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Unternehmen gelöscht.');
    }
}
