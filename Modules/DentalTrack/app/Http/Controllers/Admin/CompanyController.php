<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Models\Company;

class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::orderByDesc('created_at')->paginate(20);

        return view('dentaltrack::admin.companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('dentaltrack::admin.companies.form', ['company' => new Company]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Company::create($validated);

        return redirect()->route('dentaltrack.admin.companies.index')->with('success', __('Company created.'));
    }

    public function edit(Company $company): View
    {
        return view('dentaltrack::admin.companies.form', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $company->update($validated);

        return redirect()->route('dentaltrack.admin.companies.index')->with('success', __('Company updated.'));
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return back()->with('success', __('Company deleted.'));
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'settings' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ];
    }
}
