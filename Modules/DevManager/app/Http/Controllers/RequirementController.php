<?php

namespace Modules\DevManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\DevManager\Models\Idea;
use Modules\DevManager\Models\Requirement;

class RequirementController extends Controller
{
    public function index(Idea $idea)
    {
        $requirements = $idea->requirements()->orderBy('position')->latest()->paginate(25);

        return view('devmanager::requirements.index', compact('idea', 'requirements'));
    }

    public function create(Idea $idea)
    {
        return view('devmanager::requirements.form', compact('idea'))->with('requirement', null);
    }

    public function store(Request $request, Idea $idea)
    {
        $data = $this->validateRequirement($request);
        $data['idea_id'] = $idea->id;
        $data['position'] = $idea->requirements()->max('position') + 1;

        $requirement = $idea->requirements()->create($data);

        return redirect()->route('devmanager.requirements.index', $idea)->with('message', __('Requirement created.'));
    }

    public function show(Requirement $requirement)
    {
        return view('devmanager::requirements.show', compact('requirement'));
    }

    public function edit(Requirement $requirement)
    {
        return view('devmanager::requirements.form', ['idea' => $requirement->idea, 'requirement' => $requirement]);
    }

    public function update(Request $request, Requirement $requirement)
    {
        $data = $this->validateRequirement($request);

        $requirement->update($data);

        return redirect()->route('devmanager.requirements.index', $requirement->idea)->with('message', __('Requirement updated.'));
    }

    public function destroy(Requirement $requirement)
    {
        $idea = $requirement->idea;
        $requirement->delete();

        return redirect()->route('devmanager.requirements.index', $idea)->with('message', __('Requirement deleted.'));
    }

    protected function validateRequirement(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:draft,review,done'],
        ]);
    }
}
