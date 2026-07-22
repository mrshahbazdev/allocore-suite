<?php

namespace Modules\TimeButler\Services;

use App\Models\Team;
use App\Models\User;
use Carbon\CarbonPeriod;
use Modules\TimeButler\Models\AbsenceRequest;
use Modules\TimeButler\Models\AbsenceType;
use Modules\TimeButler\Models\VacationBalance;

class VacationBalanceService
{
    public function recalculate(User $user, Team $team, int $year): VacationBalance
    {
        $total = (float) (VacationBalance::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->value('total_days') ?? 0);

        $taken = AbsenceRequest::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->whereHas('absenceType', function ($query): void {
                $query->where('deducts_vacation', true);
            })
            ->sum('total_days');

        $requested = AbsenceRequest::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereYear('start_date', $year)
            ->whereHas('absenceType', function ($query): void {
                $query->where('deducts_vacation', true);
            })
            ->sum('total_days');

        return VacationBalance::updateOrCreate(
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'year' => $year,
            ],
            [
                'total_days' => $total,
                'taken_days' => $taken,
                'requested_days' => $requested,
                'remaining_days' => max(0, $total - $taken - $requested),
            ]
        );
    }

    public static function calculateDays(string $start, string $end, bool $halfDayStart, bool $halfDayEnd, ?callable $isHoliday = null): float
    {
        $period = CarbonPeriod::create($start, $end);
        $days = 0.0;

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            if ($isHoliday && $isHoliday($date)) {
                continue;
            }

            $isFirst = $date->format('Y-m-d') === $start;
            $isLast = $date->format('Y-m-d') === $end;

            if (($isFirst && $halfDayStart) || ($isLast && $halfDayEnd)) {
                $days += 0.5;
            } else {
                $days += 1.0;
            }
        }

        return $days;
    }

    public function seedDefaultTypes(int $teamId, ?int $userId = null): void
    {
        $defaults = [
            ['name' => 'Vacation', 'color' => '#22c55e', 'requires_approval' => true, 'is_paid' => true, 'deducts_vacation' => true],
            ['name' => 'Sick', 'color' => '#ef4444', 'requires_approval' => false, 'is_paid' => true, 'deducts_vacation' => false],
            ['name' => 'Sick Child', 'color' => '#f97316', 'requires_approval' => false, 'is_paid' => true, 'deducts_vacation' => false],
            ['name' => 'Home Office', 'color' => '#3b82f6', 'requires_approval' => false, 'is_paid' => true, 'deducts_vacation' => false],
            ['name' => 'Business Trip', 'color' => '#8b5cf6', 'requires_approval' => true, 'is_paid' => true, 'deducts_vacation' => false],
            ['name' => 'Parental Leave', 'color' => '#ec4899', 'requires_approval' => true, 'is_paid' => false, 'deducts_vacation' => false],
            ['name' => 'Special Leave', 'color' => '#06b6d4', 'requires_approval' => true, 'is_paid' => true, 'deducts_vacation' => false],
            ['name' => 'Continuing Education', 'color' => '#eab308', 'requires_approval' => true, 'is_paid' => true, 'deducts_vacation' => false],
        ];

        foreach ($defaults as $index => $type) {
            AbsenceType::firstOrCreate(
                ['team_id' => $teamId, 'name' => $type['name']],
                $type + ['user_id' => $userId, 'is_active' => true, 'sort_order' => $index]
            );
        }
    }
}
