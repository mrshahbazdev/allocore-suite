<?php

namespace Modules\TimeButler\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\TimeButler\Models\Holiday;

class GermanHolidayService
{
    public const FEDERAL_STATES = [
        'BW' => 'Baden-Württemberg',
        'BY' => 'Bayern',
        'BE' => 'Berlin',
        'BB' => 'Brandenburg',
        'HB' => 'Bremen',
        'HH' => 'Hamburg',
        'HE' => 'Hessen',
        'MV' => 'Mecklenburg-Vorpommern',
        'NI' => 'Niedersachsen',
        'NW' => 'Nordrhein-Westfalen',
        'RP' => 'Rheinland-Pfalz',
        'SL' => 'Saarland',
        'SN' => 'Sachsen',
        'ST' => 'Sachsen-Anhalt',
        'SH' => 'Schleswig-Holstein',
        'TH' => 'Thüringen',
    ];

    public function importPublicHolidays(int $teamId, string $state, int $year): int
    {
        $holidays = $this->fetchPublicHolidays($state, $year);
        $count = 0;

        foreach ($holidays as $date => $name) {
            Holiday::updateOrCreate(
                [
                    'team_id' => $teamId,
                    'date' => $date,
                    'type' => 'public',
                    'federal_state' => $state,
                ],
                [
                    'name' => $name,
                    'year' => $year,
                ]
            );

            $count++;
        }

        return $count;
    }

    public function importWeekends(int $teamId, int $year): int
    {
        $start = Carbon::create($year, 1, 1);
        $end = Carbon::create($year, 12, 31);
        $count = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (! $date->isWeekend()) {
                continue;
            }

            Holiday::updateOrCreate(
                [
                    'team_id' => $teamId,
                    'date' => $date->format('Y-m-d'),
                    'type' => 'weekend',
                    'federal_state' => null,
                ],
                [
                    'name' => $date->isSaturday() ? __('Saturday') : __('Sunday'),
                    'year' => $year,
                ]
            );

            $count++;
        }

        return $count;
    }

    private function fetchPublicHolidays(string $state, int $year): array
    {
        $url = "https://feiertage-api.de/api/v1/jahr/{$year}/{$state}";

        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json() ?? [];
            $holidays = [];

            foreach ($data as $key => $value) {
                if (is_array($value) && isset($value['datum'])) {
                    $holidays[$value['datum']] = $value['hinweis'] ?? $key;
                }
            }

            return $holidays;
        } catch (\Throwable $e) {
            Log::error('German holiday import failed', ['error' => $e->getMessage()]);

            return [];
        }
    }
}
