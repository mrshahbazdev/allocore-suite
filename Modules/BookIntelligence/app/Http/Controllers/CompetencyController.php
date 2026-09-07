<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\CareerPath;
use Modules\BookIntelligence\Models\CompetencyRole;
use Modules\BookIntelligence\Services\CompetencyFrameworkService;

class CompetencyController extends Controller
{
    public function index(CompetencyFrameworkService $service): View
    {
        $teamId = auth()->user()?->currentTeam?->id ?? 1;
        $roles = $service->ensureDefaultRoles($teamId);
        $careerPaths = CareerPath::query()->with(['fromRole', 'toRole'])->get();

        return view('bookintelligence::competency.index', compact('roles', 'careerPaths'));
    }

    public function showRole(CompetencyRole $role): View
    {
        $role->loadMissing(['nextRole', 'careerPathsFrom.toRole', 'careerPathsTo.fromRole', 'assessments', 'challenges']);

        return view('bookintelligence::competency.role-show', compact('role'));
    }
}
