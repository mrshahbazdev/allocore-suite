<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StatusIncident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusIncidentController extends Controller
{
    public function index(): View
    {
        $incidents = StatusIncident::latest('started_at')->paginate(20);

        return view('admin.status-incidents.index', compact('incidents'));
    }

    public function create(): View
    {
        return view('admin.status-incidents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'in:critical,major,minor,maintenance'],
            'status' => ['required', 'in:investigating,identified,monitoring,resolved'],
            'started_at' => ['required', 'date'],
        ]);

        $isResolved = $validated['status'] === 'resolved';

        StatusIncident::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'is_resolved' => $isResolved,
            'resolved_at' => $isResolved ? now() : null,
        ]);

        return redirect()->route('admin.status-incidents.index')->with('success', __('Incident created.'));
    }

    public function edit(StatusIncident $statusIncident): View
    {
        return view('admin.status-incidents.edit', compact('statusIncident'));
    }

    public function update(Request $request, StatusIncident $statusIncident): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'in:critical,major,minor,maintenance'],
            'status' => ['required', 'in:investigating,identified,monitoring,resolved'],
            'started_at' => ['required', 'date'],
        ]);

        $wasResolved = $statusIncident->is_resolved;
        $isResolved = $validated['status'] === 'resolved';

        $statusIncident->update([
            ...$validated,
            'is_resolved' => $isResolved,
            'resolved_at' => $isResolved && ! $wasResolved ? now() : ($isResolved ? $statusIncident->resolved_at : null),
        ]);

        return redirect()->route('admin.status-incidents.index')->with('success', __('Incident updated.'));
    }

    public function destroy(StatusIncident $statusIncident): RedirectResponse
    {
        $statusIncident->delete();

        return redirect()->route('admin.status-incidents.index')->with('success', __('Incident deleted.'));
    }

    public function resolve(StatusIncident $statusIncident): RedirectResponse
    {
        $statusIncident->resolve();

        return redirect()->route('admin.status-incidents.index')->with('success', __('Incident marked as resolved.'));
    }
}
