<?php

namespace Modules\DevManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DevManager\Models\Idea;
use Modules\DevManager\Models\Milestone;
use Modules\DevManager\Models\Release;

class RoadmapController extends Controller
{
    public function index()
    {
        $milestones = Milestone::with('idea')->orderBy('due_date')->latest()->limit(50)->get();
        $releases = Release::with('idea')->orderBy('released_at', 'desc')->latest()->limit(50)->get();
        $ideas = Idea::whereIn('status', ['approved', 'in_progress'])->latest()->limit(20)->get();

        return view('devmanager::roadmap.index', compact('milestones', 'releases', 'ideas'));
    }
}
