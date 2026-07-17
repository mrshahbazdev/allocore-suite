<?php

namespace Modules\LeadQuality\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\LeadQuality\Models\Contact;
use Modules\LeadQuality\Services\AiLeadScoringService;

class AnalyzeContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $teamId) {}

    public function handle(AiLeadScoringService $aiService): void
    {
        Contact::where('team_id', $this->teamId)->chunkById(50, function ($contacts) use ($aiService) {
            foreach ($contacts as $contact) {
                $analysis = $aiService->analyze($contact);
                $contact->update([
                    'ai_high_probability' => $analysis['high_probability'],
                    'score' => $analysis['score'],
                ]);
            }
        });
    }
}
