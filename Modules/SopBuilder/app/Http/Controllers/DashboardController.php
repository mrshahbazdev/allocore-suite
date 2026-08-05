<?php

namespace Modules\SopBuilder\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\SopBuilder\Models\Category;
use Modules\SopBuilder\Models\Completion;
use Modules\SopBuilder\Models\Evidence;
use Modules\SopBuilder\Models\Sop;

class DashboardController extends Controller
{
    public function index()
    {
        $teamId = auth()->user()->current_team_id;

        $stats = [
            'sops' => Sop::where('team_id', $teamId)->count(),
            'published' => Sop::where('team_id', $teamId)->where('status', 'published')->count(),
            'categories' => Category::where('team_id', $teamId)->count(),
            'completions' => Completion::whereHas('sop', fn ($q) => $q->where('team_id', $teamId))->count(),
            'evidence' => Evidence::whereHas('sop', fn ($q) => $q->where('team_id', $teamId))->count(),
        ];

        $recentSops = Sop::with('category')
            ->where('team_id', $teamId)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $recentCompletions = Completion::with(['sop', 'user'])
            ->whereHas('sop', fn ($q) => $q->where('team_id', $teamId))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('sopbuilder::dashboard.index', compact('stats', 'recentSops', 'recentCompletions'));
    }
}
