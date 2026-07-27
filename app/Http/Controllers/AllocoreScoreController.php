<?php

namespace App\Http\Controllers;

use App\Services\AllocoreScoreService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AllocoreScoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $score = AllocoreScoreService::latestForTeam($request->user()?->current_team_id);
        $history = AllocoreScoreService::historyForTeam($request->user()?->current_team_id, 24);

        return view('allocore-score', compact('score', 'history'));
    }
}
