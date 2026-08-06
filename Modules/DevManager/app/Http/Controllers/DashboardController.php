<?php

namespace Modules\DevManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DevManager\Models\Idea;
use Modules\DevManager\Models\UserStory;

class DashboardController extends Controller
{
    public function index()
    {
        $ideas = Idea::withCount(['requirements', 'userStories', 'milestones', 'releases'])
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'ideas' => Idea::count(),
            'requirements' => Idea::join('devmanager_requirements', 'devmanager_requirements.idea_id', '=', 'devmanager_ideas.id')->count(),
            'user_stories' => UserStory::count(),
            'done_stories' => UserStory::where('status', 'done')->count(),
            'milestones' => Idea::join('devmanager_milestones', 'devmanager_milestones.idea_id', '=', 'devmanager_ideas.id')->count(),
            'releases' => Idea::join('devmanager_releases', 'devmanager_releases.idea_id', '=', 'devmanager_ideas.id')->count(),
        ];

        return view('devmanager::dashboard.index', compact('ideas', 'stats'));
    }
}
