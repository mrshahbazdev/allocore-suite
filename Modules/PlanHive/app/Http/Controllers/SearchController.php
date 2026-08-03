<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\PlanHive\Models\Contact;
use Modules\PlanHive\Models\Project;
use Modules\PlanHive\Models\Task;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $term = $request->get('q', '');

        $projects = collect();
        $tasks = collect();
        $contacts = collect();

        if ($term !== '') {
            $projects = Project::query()
                ->where(function ($query) use ($term): void {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                })
                ->limit(10)
                ->get();

            $tasks = Task::query()
                ->where(function ($query) use ($term): void {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                })
                ->with('project')
                ->limit(10)
                ->get();

            $contacts = Contact::query()
                ->where(function ($query) use ($term): void {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('company', 'like', "%{$term}%");
                })
                ->with('project')
                ->limit(10)
                ->get();
        }

        return view('planhive::search.index', compact('projects', 'tasks', 'contacts', 'term'));
    }
}
