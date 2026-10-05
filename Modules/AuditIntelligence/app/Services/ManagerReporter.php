<?php

namespace Modules\AuditIntelligence\Services;

use App\Models\SiteSetting;
use App\Models\Team;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AuditIntelligence\Models\Recommendation;
use Throwable;

class ManagerReporter
{
    /** Push an audit-based coach suggestion to the Allocore Manager. */
    public function suggestion(Recommendation $recommendation): void
    {
        $this->push([
            'type' => 'audit.suggestion',
            'company_key' => $this->companyKey($recommendation),
            'user_email' => auth()->user()?->email,
            'payload' => [
                'ref_id' => (string) $recommendation->id,
                'issue' => $recommendation->issue,
                'solution' => $recommendation->solution,
                'responsible' => $recommendation->responsible,
                'effort' => $recommendation->effort,
                'status' => $recommendation->status,
                'finding_title' => $recommendation->finding?->title ?? $recommendation->finding?->issue,
                'occurred_at' => optional($recommendation->updated_at)->toIso8601String(),
            ],
        ]);
    }

    private function companyKey(Recommendation $recommendation): ?string
    {
        $team = Team::find($recommendation->team_id);

        return $team?->name ?? ($recommendation->team_id ? 'team-'.$recommendation->team_id : null);
    }

    private function push(array $body): void
    {
        $url = SiteSetting::value('manager.ingest_url') ?: config('services.manager.ingest_url');

        if (! is_string($url) || $url === '') {
            return;
        }

        try {
            $response = Http::timeout(5)->acceptJson()->post($url, $body);

            if (! $response->successful()) {
                Log::warning('Manager suggestion push rejected', [
                    'status' => $response->status(),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Manager suggestion push failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
