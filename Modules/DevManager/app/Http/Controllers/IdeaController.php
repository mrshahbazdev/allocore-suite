<?php

namespace Modules\DevManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\DevManager\Models\Idea;

class IdeaController extends Controller
{
    public function index()
    {
        $ideas = Idea::withCount(['requirements', 'userStories'])->latest()->paginate(20);

        return view('devmanager::ideas.index', compact('ideas'));
    }

    public function create()
    {
        return view('devmanager::ideas.form', ['idea' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateIdea($request);

        $idea = Idea::create($data['idea']);

        return redirect()->route('devmanager.ideas.show', $idea)->with('message', __('Idea created.'));
    }

    public function show(Idea $idea)
    {
        $idea->load(['requirements', 'userStories', 'milestones', 'releases', 'integrations']);

        return view('devmanager::ideas.show', compact('idea'));
    }

    public function edit(Idea $idea)
    {
        return view('devmanager::ideas.form', compact('idea'));
    }

    public function update(Request $request, Idea $idea)
    {
        $data = $this->validateIdea($request);

        $idea->update($data['idea']);

        return redirect()->route('devmanager.ideas.show', $idea)->with('message', __('Idea updated.'));
    }

    public function destroy(Idea $idea)
    {
        $idea->delete();

        return redirect()->route('devmanager.ideas.index')->with('message', __('Idea deleted.'));
    }

    protected function validateIdea(Request $request): array
    {
        return $request->validate([
            'idea.title' => ['required', 'string', 'max:255'],
            'idea.description' => ['nullable', 'string'],
            'idea.problem' => ['nullable', 'string'],
            'idea.audience' => ['nullable', 'string'],
            'idea.value' => ['nullable', 'string'],
            'idea.cost_of_problem' => ['nullable', 'string'],
            'idea.status' => ['required', 'in:draft,approved,in_progress,completed,archived'],
            'idea.started_at' => ['nullable', 'date'],
            'idea.completed_at' => ['nullable', 'date'],
        ]);
    }
}
