<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\User;
use App\Services\AllocoreScoreService;
use Illuminate\Console\Command;

class DataQualityReport extends Command
{
    protected $signature = 'data:quality-report';

    protected $description = 'Report data readiness issues for users, teams and scores.';

    public function handle(): int
    {
        $unverified = User::whereNull('email_verified_at')->count();
        $totalUsers = User::count();

        $teams = Team::all();
        $missingCluster = $teams->filter(fn (Team $team) => blank($team->industry) || blank($team->size) || blank($team->country) || blank($team->revenue_range)
        );

        $teamsWithoutScore = $teams->filter(fn (Team $team) => AllocoreScoreService::latestForTeam($team->id) === null
        );

        $this->info('Allocore data quality report');
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total users', $totalUsers],
                ['Unverified users', $unverified],
                ['Total companies', $teams->count()],
                ['Companies missing cluster data', $missingCluster->count()],
                ['Companies without Allocore Score', $teamsWithoutScore->count()],
            ]
        );

        if ($missingCluster->isNotEmpty()) {
            $this->warn('Companies missing cluster data:');
            $this->table(
                ['ID', 'Name', 'Industry', 'Size', 'Country', 'Revenue range'],
                $missingCluster->map(fn (Team $team) => [
                    $team->id,
                    $team->name,
                    $team->industry ?? '—',
                    $team->size ?? '—',
                    $team->country ?? '—',
                    $team->revenue_range ?? '—',
                ])->toArray()
            );
        }

        return self::SUCCESS;
    }
}
