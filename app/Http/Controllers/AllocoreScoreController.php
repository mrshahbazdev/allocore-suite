<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\Team;
use App\Services\AllocoreRecommendationService;
use App\Services\AllocoreScoreService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class AllocoreScoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $score = AllocoreScoreService::latestForTeam($user?->current_team_id);
        $history = AllocoreScoreService::historyForTeam($user?->current_team_id, 24);
        $recommendations = app(AllocoreRecommendationService::class)->forScore($score, $user);
        $team = $user?->currentTeam;

        return view('allocore-score', compact('score', 'history', 'recommendations', 'team'));
    }

    public function public(string $slug)
    {
        $team = Team::where('public_score_enabled', true)
            ->where('public_score_slug', $slug)
            ->firstOrFail();

        $score = AllocoreScoreService::latestForTeam($team->id);

        if (! $score) {
            abort(404);
        }

        $this->setPublicBranding($team);

        return view('scorecard', compact('team', 'score'));
    }

    public function updatePublic(Request $request)
    {
        $user = $request->user();
        $team = $user->currentTeam;

        abort_if(! $team || $team->owner_id !== $user->id, 403);

        $validated = $request->validate([
            'public_score_enabled' => 'boolean',
            'public_score_slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('teams', 'public_score_slug')->ignore($team->id),
            ],
        ]);

        $validated['public_score_slug'] = $validated['public_score_slug'] ?: null;

        $team->update($validated);

        return back()->with('success', __('Public scorecard settings updated.'));
    }

    private function setPublicBranding(Team $team): void
    {
        $fallback = SiteSetting::value('site_name', config('app.name', 'Allocore Suite'));

        config([
            'app.team_branding' => [
                'name' => $team->name ?: $fallback,
                'logo' => $team->logo,
                'favicon' => $team->favicon,
                'primary_color' => $team->primary_color ?: '#4f46e5',
                'accent_color' => $team->accent_color,
            ],
        ]);
    }
}
