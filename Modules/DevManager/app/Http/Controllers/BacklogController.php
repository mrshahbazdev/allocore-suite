<?php

namespace Modules\DevManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DevManager\Models\UserStory;

class BacklogController extends Controller
{
    public function index()
    {
        $stories = UserStory::with(['idea', 'requirement'])
            ->orderBy('status')
            ->orderBy('position')
            ->latest()
            ->paginate(50);

        $statuses = ['todo', 'in_progress', 'done'];

        return view('devmanager::backlog.index', compact('stories', 'statuses'));
    }
}
