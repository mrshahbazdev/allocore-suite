<?php

namespace Modules\DevManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\DevManager\Models\Idea;
use Modules\DevManager\Models\Milestone;

class MilestoneController extends Controller
{
    public function index(Idea $idea)
    {
        $milestones = $idea->milestones()->orderBy('due_date')->orderBy('position')->paginate(25);

        return view('devmanager::milestones.index', compact('idea', 'milestones'));
    }

    public function create(Idea $idea)
    {
        return view('devmanager::milestones.form', compact('idea'))->with('milestone', null);
    }

    public function store(Request $request, Idea $idea)
    {
        $data = $this->validateMilestone($request);
        $data['idea_id'] = $idea->id;
        $data['position'] = $idea->milestones()->max('position') + 1;

        $idea->milestones()->create($data);

        return redirect()->route('devmanager.milestones.index', $idea)->with('message', __('Milestone created.'));
    }

    public function show(Milestone $milestone)
    {
        return view('devmanager::milestones.show', compact('milestone'));
    }

    public function edit(Milestone $milestone)
    {
        return view('devmanager::milestones.form', ['idea' => $milestone->idea, 'milestone' => $milestone]);
    }

    public function update(Request $request, Milestone $milestone)
    {
        $data = $this->validateMilestone($request);

        $milestone->update($data);

        return redirect()->route('devmanager.milestones.index', $milestone->idea)->with('message', __('Milestone updated.'));
    }

    public function destroy(Milestone $milestone)
    {
        $idea = $milestone->idea;
        $milestone->delete();

        return redirect()->route('devmanager.milestones.index', $idea)->with('message', __('Milestone deleted.'));
    }

    protected function validateMilestone(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:planned,active,completed'],
        ]);
    }
}
