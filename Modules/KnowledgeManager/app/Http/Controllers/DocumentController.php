<?php

namespace Modules\KnowledgeManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\KnowledgeManager\Models\Project;

class DocumentController extends Controller
{
    public function index(Project $project)
    {
        $documents = collect(config('knowledgemanager.documents'));

        return view('knowledgemanager::documents.index', compact('project', 'documents'));
    }

    public function show(Project $project, string $type)
    {
        $documents = config('knowledgemanager.documents');

        if (! isset($documents[$type])) {
            abort(404);
        }

        $document = $documents[$type];
        $project->load(['answers', 'assets']);

        $answers = $project->answers->groupBy('section');
        $assets = $project->assets->groupBy('type');

        return view('knowledgemanager::documents.show', compact('project', 'type', 'document', 'answers', 'assets'));
    }
}
