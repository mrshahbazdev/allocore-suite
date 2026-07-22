<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Models\Company;
use Modules\DentalTrack\Models\Lab;

class LabController extends Controller
{
    public function index(): View
    {
        $labs = Lab::with('company')->orderByDesc('created_at')->paginate(20);

        return view('dentaltrack::admin.labs.index', compact('labs'));
    }

    public function create(): View
    {
        $companies = Company::where('is_active', true)->get();

        return view('dentaltrack::admin.labs.form', compact('companies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Lab::create($validated);

        return redirect()->route('dentaltrack.admin.labs.index')->with('success', __('Lab created.'));
    }

    public function edit(Lab $lab): View
    {
        $companies = Company::where('is_active', true)->get();

        return view('dentaltrack::admin.labs.form', compact('lab', 'companies'));
    }

    public function update(Request $request, Lab $lab): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $lab->update($validated);

        return redirect()->route('dentaltrack.admin.labs.index')->with('success', __('Lab updated.'));
    }

    public function destroy(Lab $lab): RedirectResponse
    {
        $lab->delete();

        return back()->with('success', __('Lab deleted.'));
    }

    private function rules(): array
    {
        return [
            'dentaltrack_company_id' => 'required|exists:dentaltrack_companies,id',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
