<?php

namespace Modules\SmartKpi\Services;

use Modules\SmartKpi\Models\KpiDefinition;
use Modules\SmartKpi\Models\KpiValue;
use Modules\SmartKpi\Models\Problem;

class ProblemDetectionService
{
    public function detect(KpiValue $value): ?Problem
    {
        $kpi = $value->kpiDefinition;

        if (! $kpi) {
            return null;
        }

        $status = $kpi->statusForValue((float) $value->value);
        $problem = null;

        if ($status === 'critical') {
            $problem = $this->createProblem($kpi, $value, 'critical', 'Threshold breach: value is in critical range.');
        } elseif ($status === 'warning') {
            if ($this->isThreeConsecutiveWarning($kpi)) {
                $problem = $this->createProblem($kpi, $value, 'warning', 'Three consecutive warning values detected.');
            }
        }

        if ($this->isZScoreAnomaly($kpi, (float) $value->value)) {
            $problem = $this->createProblem($kpi, $value, 'anomaly', 'Z-score anomaly detected.');
        }

        return $problem;
    }

    public function evaluateAlertRules(KpiDefinition $kpi, KpiValue $value): void
    {
        foreach ($kpi->alertRules()->active()->get() as $rule) {
            $triggered = false;

            switch ($rule->threshold_type) {
                case 'above':
                    $triggered = $value->value > $rule->threshold_value;
                    break;
                case 'below':
                    $triggered = $value->value < $rule->threshold_value;
                    break;
                case 'equals':
                    $triggered = (float) $value->value === (float) $rule->threshold_value;
                    break;
            }

            if ($triggered) {
                $this->createProblem($kpi, $value, $rule->severity, "Alert rule triggered: {$rule->threshold_type} {$rule->threshold_value}");
            }
        }
    }

    private function createProblem(KpiDefinition $kpi, KpiValue $value, string $severity, string $description): Problem
    {
        return Problem::firstOrCreate(
            [
                'kpi_definition_id' => $kpi->id,
                'team_id' => $kpi->team_id,
                'status' => 'open',
                'severity' => $severity,
                'title' => __(':kpi is :severity', ['kpi' => $kpi->localizedName(), 'severity' => $severity]),
            ],
            [
                'company_id' => $kpi->company_id,
                'department_id' => $kpi->department_id,
                'detected_by' => auth()->id(),
                'description' => $description,
                'detected_at' => $value->recorded_at,
            ]
        );
    }

    private function isThreeConsecutiveWarning(KpiDefinition $kpi): bool
    {
        $values = $kpi->values()->take(3)->get();

        if ($values->count() < 3) {
            return false;
        }

        return $values->every(fn (KpiValue $v) => $kpi->statusForValue((float) $v->value) === 'warning');
    }

    private function isZScoreAnomaly(KpiDefinition $kpi, float $current): bool
    {
        $values = $kpi->values()->pluck('value')->map(fn ($v) => (float) $v);

        if ($values->count() < 6) {
            return false;
        }

        $mean = $values->avg();
        $std = sqrt($values->map(fn ($v) => ($v - $mean) ** 2)->avg());

        if ($std == 0) {
            return false;
        }

        return abs(($current - $mean) / $std) > 2.5;
    }
}
