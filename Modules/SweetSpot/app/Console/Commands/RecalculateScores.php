<?php

namespace Modules\SweetSpot\Console\Commands;

use Illuminate\Console\Command;
use Modules\SweetSpot\Services\SweetSpotScoringService;

class RecalculateScores extends Command
{
    protected $signature = 'sweet-spot:recalculate {team?}';

    protected $description = 'Recalculate SweetSpot customer scores';

    public function handle(SweetSpotScoringService $service): int
    {
        $teamId = $this->argument('team');

        $service->calculateAll($teamId ? (int) $teamId : null);

        $this->info('SweetSpot scores recalculated.');

        return self::SUCCESS;
    }
}
