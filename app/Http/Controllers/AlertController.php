<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Support\AlertEvaluator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $alerts = Alert::where('user_id', $request->user()->id)
            ->where('team_id', $request->user()->current_team_id)
            ->latest()
            ->get();

        return view('alerts.index', compact('alerts'));
    }

    public function create()
    {
        return view('alerts.form', ['alert' => new Alert]);
    }

    public function store(Request $request)
    {
        $data = $this->validateAlert($request);
        $data['user_id'] = $request->user()->id;
        $data['team_id'] = $request->user()->current_team_id;

        Alert::create($data);

        return redirect()->route('alerts.index')->with('success', __('Alert created.'));
    }

    public function edit(Alert $alert)
    {
        $this->authorizeAccess($alert);

        return view('alerts.form', compact('alert'));
    }

    public function update(Request $request, Alert $alert)
    {
        $this->authorizeAccess($alert);

        $alert->update($this->validateAlert($request, $alert));

        return redirect()->route('alerts.index')->with('success', __('Alert updated.'));
    }

    public function destroy(Alert $alert)
    {
        $this->authorizeAccess($alert);
        $alert->delete();

        return redirect()->route('alerts.index')->with('success', __('Alert deleted.'));
    }

    public function test(Request $request, Alert $alert, AlertEvaluator $evaluator)
    {
        $this->authorizeAccess($alert);

        $value = $evaluator->evaluate($alert, $alert->team_id);

        return back()->with('success', __('Test value: :value', ['value' => number_format($value ?? 0, 2)]));
    }

    protected function validateAlert(Request $request, ?Alert $alert = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'metric' => ['required', Rule::in(['overdue_invoices', 'low_cash', 'kpi_critical', 'pending_absences', 'custom'])],
            'operator' => ['required', Rule::in(['>', '<', '>=', '<=', '='])],
            'threshold' => 'required|numeric',
            'notification_method' => ['required', Rule::in(['in_app', 'email'])],
            'is_active' => 'boolean',
        ]);
    }

    protected function authorizeAccess(Alert $alert): void
    {
        abort_if($alert->user_id !== auth()->id() || $alert->team_id !== auth()->user()->current_team_id, 403);
    }
}
