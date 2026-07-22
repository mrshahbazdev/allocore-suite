<?php

namespace Modules\SmartKpi\Services;

use Modules\SmartKpi\Models\Forecast;
use Modules\SmartKpi\Models\KpiDefinition;

class ForecastService
{
    public function forecast(KpiDefinition $kpi, string $horizon = '3m', string $method = 'linear'): Forecast
    {
        $values = $kpi->values()->orderBy('recorded_at')->get();

        if ($values->isEmpty()) {
            return $this->createForecast($kpi, $horizon, $method, null, null, null);
        }

        $points = $values->values()->map(fn ($v, $i) => ['x' => $i, 'y' => (float) $v->value])->all();

        switch ($method) {
            case 'exponential':
                $forecast = $this->exponentialSmoothing($points);
                break;
            case 'linear':
            default:
                $forecast = $this->linearRegression($points);
                break;
        }

        $std = $this->std($points);

        return $this->createForecast(
            $kpi,
            $horizon,
            $method,
            $forecast,
            $forecast !== null ? $forecast - $std : null,
            $forecast !== null ? $forecast + $std : null
        );
    }

    private function linearRegression(array $points): ?float
    {
        $n = count($points);
        $sumX = array_sum(array_column($points, 'x'));
        $sumY = array_sum(array_column($points, 'y'));
        $sumXY = array_sum(array_map(fn ($p) => $p['x'] * $p['y'], $points));
        $sumX2 = array_sum(array_map(fn ($p) => $p['x'] ** 2, $points));
        $denominator = $n * $sumX2 - $sumX ** 2;

        if ($denominator == 0) {
            return null;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $intercept = ($sumY - $slope * $sumX) / $n;

        return $intercept + $slope * ($n + 1);
    }

    private function exponentialSmoothing(array $points): ?float
    {
        $alpha = 0.3;
        $forecast = $points[0]['y'];

        for ($i = 1; $i < count($points); $i++) {
            $forecast = $alpha * $points[$i]['y'] + (1 - $alpha) * $forecast;
        }

        return $forecast;
    }

    private function std(array $points): float
    {
        $values = array_column($points, 'y');
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $values)) / count($values);

        return sqrt($variance);
    }

    private function createForecast(KpiDefinition $kpi, string $horizon, string $method, ?float $value, ?float $lower, ?float $upper): Forecast
    {
        return Forecast::create([
            'team_id' => $kpi->team_id,
            'kpi_definition_id' => $kpi->id,
            'forecasted_at' => now(),
            'horizon' => $horizon,
            'method' => $method,
            'value' => $value,
            'confidence_lower' => $lower,
            'confidence_upper' => $upper,
        ]);
    }
}
