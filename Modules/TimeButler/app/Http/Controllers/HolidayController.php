<?php

namespace Modules\TimeButler\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\TimeButler\Models\Holiday;
use Modules\TimeButler\Services\GermanHolidayService;

class HolidayController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->get('year', now()->year);

        $holidays = Holiday::query()
            ->whereYear('date', $year)
            ->orderBy('date')
            ->paginate(50)
            ->withQueryString();

        return view('timebutler::holidays.index', [
            'holidays' => $holidays,
            'year' => $year,
            'states' => GermanHolidayService::FEDERAL_STATES,
        ]);
    }

    public function import(Request $request, GermanHolidayService $service): RedirectResponse
    {
        $validated = $request->validate([
            'state' => 'required|string|size:2',
            'year' => 'required|integer|min:2020|max:2030',
            'include_weekends' => 'nullable|boolean',
        ]);

        $team = auth()->user()->currentTeam;

        $count = $service->importPublicHolidays($team->id, $validated['state'], $validated['year']);

        if ($request->boolean('include_weekends')) {
            $count += $service->importWeekends($team->id, $validated['year']);
        }

        return back()->with('success', __('Imported :count entries.', ['count' => $count]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:public,school,weekend,custom',
            'federal_state' => 'nullable|string|max:255',
        ]);

        Holiday::create($validated + ['year' => now()->parse($validated['date'])->year]);

        return back()->with('success', __('Holiday saved.'));
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return back()->with('success', __('Holiday deleted.'));
    }
}
