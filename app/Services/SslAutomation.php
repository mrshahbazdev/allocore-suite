<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SslAutomation
{
    public function verifyDns(Team $team): bool
    {
        if (empty($team->custom_domain)) {
            return false;
        }

        $records = dns_get_record($team->custom_domain, DNS_A | DNS_AAAA);

        if (! is_array($records) || empty($records)) {
            return false;
        }

        // Accept any A/AAAA record as verification that the domain resolves.
        $team->update(['custom_domain_verified_at' => now()]);

        return true;
    }

    public function request(Team $team, ?string $commandTemplate = null): array
    {
        if (empty($team->custom_domain)) {
            return ['success' => false, 'message' => __('Team has no custom domain.')];
        }

        if (! $team->custom_domain_verified_at) {
            return ['success' => false, 'message' => __('Custom domain DNS is not verified.')];
        }

        $template = $commandTemplate ?? config('services.ssl.command');

        if (empty($template)) {
            return ['success' => false, 'message' => __('No SSL command configured. Set SERVICES_SSL_COMMAND in .env.')];
        }

        $command = str_replace(['{domain}', '{team_id}'], [$team->custom_domain, $team->id], $template);

        try {
            $result = Process::run($command);

            if (! $result->successful()) {
                $team->update(['ssl_status' => 'failed', 'ssl_last_error' => $result->errorOutput()]);

                return ['success' => false, 'message' => $result->errorOutput()];
            }

            $team->update([
                'ssl_status' => 'issued',
                'ssl_issued_at' => now(),
                'ssl_expires_at' => now()->addDays(90),
                'ssl_last_error' => null,
            ]);

            return ['success' => true, 'message' => $result->output()];
        } catch (ProcessFailedException $e) {
            $team->update(['ssl_status' => 'failed', 'ssl_last_error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
