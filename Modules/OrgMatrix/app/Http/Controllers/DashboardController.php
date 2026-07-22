<?php

namespace Modules\OrgMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\OrgMatrix\Models\Organization;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $organizations = Organization::where('user_id', $request->user()->id)
            ->withCount(['roles', 'people'])
            ->latest()
            ->get();

        $totalRoles = $organizations->sum('roles_count');
        $totalPeople = $organizations->sum('people_count');

        return view('orgmatrix::dashboard.index', [
            'organizations' => $organizations,
            'total_roles' => $totalRoles,
            'total_people' => $totalPeople,
        ]);
    }
}
