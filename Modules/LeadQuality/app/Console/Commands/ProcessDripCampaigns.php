<?php

namespace Modules\LeadQuality\Console\Commands;

use Illuminate\Console\Command;
use Modules\LeadQuality\Models\Contact;
use Modules\LeadQuality\Models\SequenceStep;
use Modules\LeadQuality\Services\LeadEmailService;
use Modules\LeadQuality\Services\TemplateService;

class ProcessDripCampaigns extends Command
{
    protected $signature = 'leadquality:process-drip-campaigns';

    protected $description = 'Process due sequence steps and send outreach emails';

    public function handle(TemplateService $templateService, LeadEmailService $emailService): int
    {
        $contacts = Contact::query()
            ->whereHas('sequences', function ($query): void {
                $query->where('status', 'active')
                    ->whereNotNull('next_run_at')
                    ->where('next_run_at', '<=', now())
                    ->whereNotNull('current_step_id');
            })
            ->with(['sequences' => function ($query): void {
                $query->wherePivot('status', 'active')
                    ->wherePivotNotNull('next_run_at')
                    ->wherePivot('next_run_at', '<=', now())
                    ->wherePivotNotNull('current_step_id');
            }])
            ->get();

        $count = 0;

        foreach ($contacts as $contact) {
            foreach ($contact->sequences as $sequence) {
                if (! $sequence->is_active) {
                    continue;
                }

                $currentStep = SequenceStep::query()->find($sequence->pivot->current_step_id);

                if (! $currentStep) {
                    continue;
                }

                $mergedBody = $templateService->merge($currentStep->body, $contact);
                $mergedSubject = $templateService->merge($currentStep->subject, $contact);

                $result = $emailService->send($contact, $mergedSubject, $mergedBody, $sequence->user);

                $notes = "Drip Campaign: {$sequence->name} (Step {$currentStep->order})\n\nSubject: {$mergedSubject}\n\n{$mergedBody}";

                $contact->activities()->create([
                    'user_id' => $sequence->team->owner_id ?? null,
                    'team_id' => $sequence->team_id,
                    'type' => 'outreach',
                    'status' => $result['success'] ? 'completed' : 'failed',
                    'scheduled_at' => now(),
                    'notes' => $notes."\n\nResult: ".($result['success'] ? 'Sent' : 'Failed - '.$result['message']),
                ]);

                if (! $result['success']) {
                    $contact->sequences()->updateExistingPivot($sequence->id, [
                        'status' => 'failed',
                    ]);

                    continue;
                }

                $count++;

                $nextStep = SequenceStep::query()
                    ->where('sequence_id', $sequence->id)
                    ->where('order', '>', $currentStep->order)
                    ->orderBy('order')
                    ->first();

                if ($nextStep) {
                    $contact->sequences()->updateExistingPivot($sequence->id, [
                        'current_step_id' => $nextStep->id,
                        'next_run_at' => now()->addDays($nextStep->delay_days),
                    ]);
                } else {
                    $contact->sequences()->updateExistingPivot($sequence->id, [
                        'current_step_id' => null,
                        'next_run_at' => null,
                        'status' => 'completed',
                    ]);
                }
            }
        }

        $this->info("Processed {$count} drip campaign steps.");

        return self::SUCCESS;
    }
}
