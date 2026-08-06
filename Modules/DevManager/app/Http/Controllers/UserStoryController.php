<?php

namespace Modules\DevManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\DevManager\Models\Idea;
use Modules\DevManager\Models\UserStory;

class UserStoryController extends Controller
{
    public function index(Idea $idea)
    {
        $stories = $idea->userStories()->with('requirement')->orderBy('position')->latest()->paginate(25);

        return view('devmanager::user_stories.index', compact('idea', 'stories'));
    }

    public function create(Idea $idea)
    {
        return view('devmanager::user_stories.form', compact('idea'))->with('story', null);
    }

    public function store(Request $request, Idea $idea)
    {
        $data = $this->validateStory($request);
        $data['idea_id'] = $idea->id;
        $data['position'] = $idea->userStories()->max('position') + 1;

        $story = $idea->userStories()->create($data);

        return redirect()->route('devmanager.user-stories.index', $idea)->with('message', __('User story created.'));
    }

    public function show(UserStory $userStory)
    {
        return view('devmanager::user_stories.show', compact('userStory'));
    }

    public function edit(UserStory $userStory)
    {
        return view('devmanager::user_stories.form', ['idea' => $userStory->idea, 'story' => $userStory]);
    }

    public function update(Request $request, UserStory $userStory)
    {
        $data = $this->validateStory($request);

        $userStory->update($data);

        return redirect()->route('devmanager.user-stories.index', $userStory->idea)->with('message', __('User story updated.'));
    }

    public function destroy(UserStory $userStory)
    {
        $idea = $userStory->idea;
        $userStory->delete();

        return redirect()->route('devmanager.user-stories.index', $idea)->with('message', __('User story deleted.'));
    }

    protected function validateStory(Request $request): array
    {
        return $request->validate([
            'role' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', 'max:255'],
            'benefit' => ['nullable', 'string', 'max:255'],
            'acceptance_criteria' => ['nullable', 'string'],
            'story_points' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:todo,in_progress,done'],
            'requirement_id' => ['nullable', 'exists:devmanager_requirements,id'],
        ]);
    }
}
