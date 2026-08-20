<?php

namespace App\Console\Commands;

use App\Services\MaturityDataSnapshotService;
use Illuminate\Console\Command;

class RefreshMaturityDataSnapshots extends Command
{
    protected $signature = 'data:refresh-snapshots';

    protected $description = 'Rebuild maturity data snapshots from the latest Allocore Score per team.';

    public function handle(): int
    {
        $count = MaturityDataSnapshotService::refreshAll();

        $this->info("Refreshed {$count} maturity data snapshots.");

        return self::SUCCESS;
    }
}
