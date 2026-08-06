<?php

namespace Modules\KnowledgeManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\KnowledgeManager\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = Project::withCount(['answers', 'assets'])
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'projects' => Project::count(),
            'published' => Project::where('status', 'published')->count(),
            'answers' => Project::join('knowledge_answers', 'knowledge_answers.project_id', '=', 'knowledge_projects.id')->whereNotNull('knowledge_answers.answer')->where('knowledge_answers.answer', '!=', '')->count(),
            'assets' => Project::join('knowledge_assets', 'knowledge_assets.project_id', '=', 'knowledge_projects.id')->count(),
        ];

        return view('knowledgemanager::dashboard.index', compact('projects', 'stats'));
    }
}
