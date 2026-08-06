<?php

namespace Modules\DevManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\DevManager\Models\Idea;
use Modules\DevManager\Models\Release;

class ReleaseController extends Controller
{
    public function index(Idea $idea)
    {
        $releases = $idea->releases()->latest('released_at')->latest()->paginate(25);

        return view('devmanager::releases.index', compact('idea', 'releases'));
    }

    public function create(Idea $idea)
    {
        return view('devmanager::releases.form', compact('idea'))->with('release', null);
    }

    public function store(Request $request, Idea $idea)
    {
        $data = $this->validateRelease($request);
        $data['idea_id'] = $idea->id;

        $idea->releases()->create($data);

        return redirect()->route('devmanager.releases.index', $idea)->with('message', __('Release created.'));
    }

    public function show(Release $release)
    {
        return view('devmanager::releases.show', compact('release'));
    }

    public function edit(Release $release)
    {
        return view('devmanager::releases.form', ['idea' => $release->idea, 'release' => $release]);
    }

    public function update(Request $request, Release $release)
    {
        $data = $this->validateRelease($request);

        $release->update($data);

        return redirect()->route('devmanager.releases.index', $release->idea)->with('message', __('Release updated.'));
    }

    public function destroy(Release $release)
    {
        $idea = $release->idea;
        $release->delete();

        return redirect()->route('devmanager.releases.index', $idea)->with('message', __('Release deleted.'));
    }

    protected function validateRelease(Request $request): array
    {
        return $request->validate([
            'version' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'changelog' => ['nullable', 'string'],
            'released_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,planned,released,archived'],
        ]);
    }
}
