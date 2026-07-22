<?php

namespace Modules\TimeButler\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\TimeButler\Models\AbsenceType;

class AbsenceTypeController extends Controller
{
    public function index(): View
    {
        $types = AbsenceType::query()->orderBy('sort_order')->get();

        return view('timebutler::absence-types.index', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'requires_approval' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'deducts_vacation' => 'nullable|boolean',
            'max_days_per_year' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        AbsenceType::create($validated);

        return back()->with('success', __('Absence type saved.'));
    }

    public function update(Request $request, AbsenceType $absenceType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'requires_approval' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'deducts_vacation' => 'nullable|boolean',
            'max_days_per_year' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $absenceType->update($validated);

        return back()->with('success', __('Absence type updated.'));
    }

    public function destroy(AbsenceType $absenceType): RedirectResponse
    {
        $absenceType->delete();

        return back()->with('success', __('Absence type deleted.'));
    }
}
