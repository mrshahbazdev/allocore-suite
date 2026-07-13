<?php

namespace Modules\LeadQuality\Services;

use Modules\LeadQuality\Models\Contact;
use Modules\LeadQuality\Models\IcpProfile;

class LeadScoreEngine
{
    public function calculateScore(Contact $contact, ?IcpProfile $profile = null): array
    {
        $profile ??= IcpProfile::first();

        if (! $profile) {
            return [
                'total_score' => 0,
                'metrics' => [],
                'status' => __('🔴 Bad Lead'),
                'issues' => [__('No ICP profile defined')],
            ];
        }

        $scores = [
            'industry' => 0,
            'company_size' => 0,
            'budget' => 0,
            'role' => 0,
            'problem_fit' => 10,
        ];

        $issues = [];

        if ($contact->industry && $profile->industry) {
            if (stripos($profile->industry, $contact->industry) !== false || stripos($contact->industry, $profile->industry) !== false) {
                $scores['industry'] = 25;
            } else {
                $issues[] = __('Industry mismatch');
            }
        }

        if ($contact->employee_count_range && $profile->employee_count_range) {
            if ($contact->employee_count_range === $profile->employee_count_range) {
                $scores['company_size'] = 20;
            } else {
                $issues[] = __('Company size outside target range');
            }
        }

        if ($contact->budget && ($profile->budget_min || $profile->budget_max)) {
            $min = $profile->budget_min ?? 0;
            $max = $profile->budget_max ?? PHP_INT_MAX;

            if ($contact->budget >= $min && $contact->budget <= $max) {
                $scores['budget'] = 25;
            } else {
                $issues[] = __('Budget outside target range');
            }
        }

        if ($contact->role && $profile->role) {
            $roles = array_map('trim', explode(',', strtolower($profile->role)));
            if (in_array(strtolower($contact->role), $roles, true)) {
                $scores['role'] = 20;
            } else {
                $issues[] = __('Non-decision maker role');
            }
        }

        $totalScore = array_sum($scores);

        $status = __('🔴 Bad Lead');
        if ($totalScore >= 70) {
            $status = __('🟢 Good Lead');
        } elseif ($totalScore >= 40) {
            $status = __('🟡 Average Lead');
        }

        return [
            'total_score' => $totalScore,
            'metrics' => $scores,
            'status' => $status,
            'issues' => $issues,
        ];
    }
}
