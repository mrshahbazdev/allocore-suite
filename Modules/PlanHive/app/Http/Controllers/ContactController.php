<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\PlanHive\Models\Contact;
use Modules\PlanHive\Models\Project;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contact::query()->with('project')->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('company', 'like', '%'.$request->search.'%');
        }

        return view('planhive::contacts.index', ['contacts' => $query->paginate(25)->withQueryString()]);
    }

    public function create(Project $project): View
    {
        return view('planhive::contacts.form', ['project' => $project, 'contact' => new Contact]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        $project->contacts()->create($validated + ['team_id' => $project->team_id, 'user_id' => auth()->id()]);

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Contact created.'));
    }

    public function edit(Contact $contact): View
    {
        return view('planhive::contacts.form', ['project' => $contact->project, 'contact' => $contact]);
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        $contact->update($validated);

        return redirect()->route('planhive.projects.show', $contact->project)->with('success', __('Contact updated.'));
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $project = $contact->project;
        $contact->delete();

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Contact deleted.'));
    }
}
