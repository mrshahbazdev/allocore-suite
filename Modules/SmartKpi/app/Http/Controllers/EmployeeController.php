<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\SmartKpi\Models\Department;
use Modules\SmartKpi\Models\Employee;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::with('department.company')->latest()->paginate(15);

        return view('smartkpi::employees.index', compact('employees'));
    }

    public function create(Department $department): View
    {
        $users = User::whereHas('teams', fn ($q) => $q->where('teams.id', auth()->user()->current_team_id))->get();

        return view('smartkpi::employees.form', ['employee' => new Employee, 'department' => $department, 'users' => $users]);
    }

    public function store(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['department_id'] = $department->id;
        $validated['company_id'] = $department->company_id;

        Employee::create($validated);

        return redirect()->route('smartkpi.departments.show', $department)->with('success', __('Employee created.'));
    }

    public function edit(Employee $employee): View
    {
        $users = User::whereHas('teams', fn ($q) => $q->where('teams.id', auth()->user()->current_team_id))->get();

        return view('smartkpi::employees.form', compact('employee', 'users'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $employee->update($validated);

        return redirect()->route('smartkpi.employees.index')->with('success', __('Employee updated.'));
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('smartkpi.employees.index')->with('success', __('Employee deleted.'));
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'user_id' => 'nullable|exists:users,id',
            'role' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
