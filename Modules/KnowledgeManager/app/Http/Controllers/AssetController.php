<?php

namespace Modules\KnowledgeManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\KnowledgeManager\Models\Asset;
use Modules\KnowledgeManager\Models\Project;

class AssetController extends Controller
{
    public function index(Project $project)
    {
        $project->load('assets');

        return view('knowledgemanager::assets.index', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'assets' => ['required', 'array'],
            'assets.*.type' => ['required', 'in:module,api,table,dependency'],
            'assets.*.name' => ['required', 'string', 'max:255'],
            'assets.*.description' => ['nullable', 'string'],
            'assets.*.link' => ['nullable', 'url'],
        ]);

        foreach ($validated['assets'] as $asset) {
            $project->assets()->create($asset + ['team_id' => $project->team_id]);
        }

        return redirect()->route('knowledgemanager.assets.index', $project)->with('message', __('Assets added.'));
    }

    public function destroy(Project $project, Asset $asset)
    {
        $asset->delete();

        return redirect()->route('knowledgemanager.assets.index', $project)->with('message', __('Asset removed.'));
    }
}
