<?php

namespace Modules\LeadQuality\Services;

use Modules\LeadQuality\Models\Contact;

class AiLeadScoringService
{
    public function analyze(Contact $contact): array
    {
        $insights = [];
        $score = 0;

        if ($contact->email) {
            $domain = substr(strrchr($contact->email, '@'), 1);

            if (! in_array(strtolower($domain), ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'], true)) {
                $score += 30;
                $insights[] = __('Professional email domain detected.');
            } else {
                $insights[] = __('Free email domain detected.');
            }
        }

        if ($contact->linkedin) {
            $score += 30;
            $insights[] = __('LinkedIn profile found.');
        }

        if ($contact->website) {
            $score += 20;
            $insights[] = __('Company website found.');
        }

        if ($contact->role && in_array(strtolower($contact->role), ['ceo', 'founder', 'director', 'manager', 'owner'], true)) {
            $score += 20;
            $insights[] = __('Decision-maker role detected.');
        }

        $highProbability = $score >= 70;

        array_unshift(
            $insights,
            $highProbability
                ? __('High probability B2B match.')
                : __('Lead needs more qualification.')
        );

        return [
            'high_probability' => $highProbability,
            'score' => $score,
            'insights' => $insights,
        ];
    }
}
