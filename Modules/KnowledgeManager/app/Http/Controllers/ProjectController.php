<?php

namespace Modules\KnowledgeManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\KnowledgeManager\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::withCount(['answers', 'assets'])->latest()->paginate(20);

        return view('knowledgemanager::projects.index', compact('projects'));
    }

    public function create()
    {
        return view('knowledgemanager::projects.form', ['project' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateProject($request);

        $project = DB::transaction(function () use ($data) {
            return Project::create($data['project']);
        });

        return redirect()->route('knowledgemanager.projects.show', $project)->with('message', __('Project created.'));
    }

    public function show(Project $project)
    {
        $project->load(['answers', 'assets']);

        return view('knowledgemanager::projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('knowledgemanager::projects.form', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $this->validateProject($request, $project);

        $project->update($data['project']);

        return redirect()->route('knowledgemanager.projects.show', $project)->with('message', __('Project updated.'));
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('knowledgemanager.projects.index')->with('message', __('Project deleted.'));
    }

    protected function validateProject(Request $request, ?Project $project = null): array
    {
        $teamId = auth()->user()->current_team_id;

        return $request->validate([
            'project.name' => ['required', 'string', 'max:255'],
            'project.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('knowledge_projects', 'slug')->ignore($project?->id)->where('team_id', $teamId),
            ],
            'project.description' => ['nullable', 'string'],
            'project.status' => ['required', 'in:draft,published,archived'],
            'project.url' => ['nullable', 'url'],
            'project.industry' => ['nullable', 'string', 'max:255'],
            'project.stage' => ['nullable', 'string', 'max:255'],
            'project.metadata' => ['nullable', 'array'],
        ], [], [
            'project.name' => __('Name'),
            'project.slug' => __('Slug'),
        ]);
    }
}
