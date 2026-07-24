<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Services\SslAutomation;
use Illuminate\Console\Command;

class RenewSsl extends Command
{
    protected $signature = 'ssl:renew {team? : Team ID or "all"}';

    protected $description = 'Verify DNS and request/renew SSL certificates for custom domains';

    public function handle(SslAutomation $ssl): int
    {
        $teamArg = $this->argument('team');

        $query = Team::whereNotNull('custom_domain');

        if ($teamArg && $teamArg !== 'all') {
            $query->where('id', $teamArg);
        }

        $teams = $query->get();

        if ($teams->isEmpty()) {
            $this->warn(__('No teams with custom domains found.'));

            return Command::FAILURE;
        }

        foreach ($teams as $team) {
            $this->info("Processing {$team->custom_domain}...");

            if (! $ssl->verifyDns($team)) {
                $this->error("DNS verification failed for {$team->custom_domain}.");

                continue;
            }

            $result = $ssl->request($team);

            if ($result['success']) {
                $this->info($result['message']);
            } else {
                $this->error($result['message']);
            }
        }

        return Command::SUCCESS;
    }
}
