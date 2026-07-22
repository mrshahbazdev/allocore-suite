<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\SmartKpi\Models\Company;

class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::latest()->paginate(15);

        return view('smartkpi::companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('smartkpi::companies.form', ['company' => new Company]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Company::create($validated);

        return redirect()->route('smartkpi.companies.index')->with('success', __('Company created.'));
    }

    public function show(Company $company): View
    {
        $company->load('departments.kpiDefinitions');

        return view('smartkpi::companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        return view('smartkpi::companies.form', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $company->update($validated);

        return redirect()->route('smartkpi.companies.index')->with('success', __('Company updated.'));
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('smartkpi.companies.index')->with('success', __('Company deleted.'));
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'industry' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ];
    }
}
