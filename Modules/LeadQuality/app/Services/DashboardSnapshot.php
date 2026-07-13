<?php

namespace Modules\LeadQuality\Services;

use App\Models\Team;
use Modules\LeadQuality\Models\Activity;
use Modules\LeadQuality\Models\Contact;
use Modules\LeadQuality\Models\Sequence;

class DashboardSnapshot
{
    public function forTeam(?Team $team): array
    {
        if (! $team) {
            return [
                'total_leads' => 0,
                'good_leads' => 0,
                'avg_score' => 0,
                'active_sequences' => 0,
                'recent_activities' => collect(),
                'pipeline' => collect(),
            ];
        }

        $contacts = Contact::query()->where('team_id', $team->id)->get();
        $scoreEngine = app(LeadScoreEngine::class);
        $scored = $contacts->map(fn (Contact $contact) => $scoreEngine->calculateScore($contact));

        return [
            'total_leads' => $contacts->count(),
            'good_leads' => $scored->where('status', '🟢 Good Lead')->count(),
            'avg_score' => (int) round($scored->avg('total_score') ?? 0),
            'active_sequences' => Sequence::query()->where('team_id', $team->id)->where('is_active', true)->count(),
            'recent_activities' => Activity::query()->where('team_id', $team->id)->with('contact')->latest()->take(5)->get(),
            'pipeline' => [
                'new' => $contacts->where('pipeline_stage', 'new')->count(),
                'contacted' => $contacts->where('pipeline_stage', 'contacted')->count(),
                'meeting_set' => $contacts->where('pipeline_stage', 'meeting_set')->count(),
                'won' => $contacts->where('pipeline_stage', 'won')->count(),
                'lost' => $contacts->where('pipeline_stage', 'lost')->count(),
            ],
        ];
    }
}
