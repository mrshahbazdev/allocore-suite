<?php

namespace App\Http\Controllers;

use App\Services\AllocoreRecommendationService;
use App\Services\AllocoreScoreService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AllocoreScoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $score = AllocoreScoreService::latestForTeam($user?->current_team_id);
        $history = AllocoreScoreService::historyForTeam($user?->current_team_id, 24);
        $recommendations = app(AllocoreRecommendationService::class)->forScore($score, $user);

        return view('allocore-score', compact('score', 'history', 'recommendations'));
    }
}
