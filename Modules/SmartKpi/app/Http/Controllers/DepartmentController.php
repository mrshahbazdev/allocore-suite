<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\SmartKpi\Models\Company;
use Modules\SmartKpi\Models\Department;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Department::query();

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $departments = $query->with('company')->latest()->paginate(15);

        return view('smartkpi::departments.index', compact('departments'));
    }

    public function create(Company $company): View
    {
        return view('smartkpi::departments.form', ['department' => new Department, 'company' => $company]);
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['company_id'] = $company->id;

        Department::create($validated);

        return redirect()->route('smartkpi.companies.show', $company)->with('success', __('Department created.'));
    }

    public function show(Department $department): View
    {
        $department->load('company', 'kpiDefinitions');

        return view('smartkpi::departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        return view('smartkpi::departments.form', compact('department'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $department->update($validated);

        return redirect()->route('smartkpi.departments.index')->with('success', __('Department updated.'));
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('smartkpi.departments.index')->with('success', __('Department deleted.'));
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:smartkpi_departments,id',
            'industry_type' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
