<?php

namespace Modules\FinancialPlatform\Services;

use Modules\FinancialPlatform\Models\Analysis;
use Modules\FinancialPlatform\Models\GmbhInput;
use Modules\FinancialPlatform\Models\KpiResult;
use Modules\FinancialPlatform\Models\KpiThreshold;

class GmbhScoringService
{
    private GmbhInput $input;

    /** @var array<string, KpiThreshold> */
    private array $thresholds = [];

    /** @var array<string, float> */
    private array $customWeights = [];

    public function __construct(GmbhInput $input)
    {
        $this->input = $input;
        $this->thresholds = KpiThreshold::query()
            ->where('tool', 'gmbh')
            ->where('is_active', true)
            ->get()
            ->keyBy('kpi_code')
            ->all();
        $this->customWeights = collect($this->input->custom_weights ?? [])
            ->mapWithKeys(fn ($value, $code) => [(string) $code => (float) $value])
            ->all();
    }

    // ─────────────────────────────────────────────────────────────
    //  KPI Calculations
    // ─────────────────────────────────────────────────────────────

    /** Umsatzwachstum in % */
    public function umsatzwachstum(): ?float
    {
        if (! $this->input->revenue_prev || $this->input->revenue_prev == 0) {
            return null;
        }

        return (($this->input->revenue_current - $this->input->revenue_prev) / $this->input->revenue_prev) * 100;
    }

    /** EBITDA-Marge in % */
    public function ebitdaMarge(): ?float
    {
        if (! $this->input->revenue_current || $this->input->revenue_current == 0) {
            return null;
        }
        $ebitda = $this->input->ebitda ?? ($this->input->net_profit + $this->input->depreciation + $this->input->interest);

        return ($ebitda / $this->input->revenue_current) * 100;
    }

    /** Debt / Equity Ratio */
    public function debtEquityRatio(): ?float
    {
        if (! $this->input->equity || $this->input->equity == 0) {
            return null;
        }

        return $this->input->total_debt / $this->input->equity;
    }

    /** Current Ratio (Liquidität 2. Grades) */
    public function currentRatio(): ?float
    {
        if (! $this->input->current_liabilities || $this->input->current_liabilities == 0) {
            return null;
        }

        return $this->input->current_assets / $this->input->current_liabilities;
    }

    /** Runway in Monaten */
    public function runway(): ?float
    {
        if (! $this->input->monthly_burn || $this->input->monthly_burn == 0) {
            return null;
        }

        return $this->input->cash / $this->input->monthly_burn;
    }

    /** LTV/CAC Ratio */
    public function ltvCacRatio(): ?float
    {
        if (! $this->input->cac || $this->input->cac == 0) {
            return null;
        }

        return $this->input->ltv / $this->input->cac;
    }

    /** Eigenkapitalquote in % */
    public function eigenkapitalQuote(): ?float
    {
        if (! $this->input->total_assets || $this->input->total_assets == 0) {
            return null;
        }

        return ($this->input->equity / $this->input->total_assets) * 100;
    }

    // ─────────────────────────────────────────────────────────────
    //  Scoring Engine (0–100)
    // ─────────────────────────────────────────────────────────────

    private function scoreKpi(float $value, array $thresholds): float
    {
        // thresholds: [green_min => 100pts, yellow_min => 60pts, else => 20pts]
        if ($value >= $thresholds['green']) {
            return 100.0;
        }
        if ($value >= $thresholds['yellow']) {
            return 60.0;
        }

        return 20.0;
    }

    private function scoreLowerIsBetter(float $value, array $thresholds): float
    {
        if ($value <= $thresholds['green']) {
            return 100.0;
        }
        if ($value <= $thresholds['yellow']) {
            return 60.0;
        }

        return 20.0;
    }

    private function evaluateKpi(string $code, ?float $value, array $fallback): array
    {
        $threshold = $this->thresholds[$code] ?? null;
        $weight = array_key_exists($code, $this->customWeights)
            ? $this->customWeights[$code]
            : ($threshold?->weight !== null ? (float) $threshold->weight : (float) $fallback['weight']);

        if ($value === null) {
            return ['weight' => $weight, 'score' => 50.0, 'traffic_light' => 'yellow'];
        }

        if ($threshold) {
            $light = $threshold->evaluate($value);
            $score = $light === 'green' ? 100.0 : ($light === 'yellow' ? 60.0 : 20.0);

            return ['weight' => $weight, 'score' => $score, 'traffic_light' => $light];
        }

        $light = $fallback['lower_is_better']
            ? ($value <= $fallback['green'] ? 'green' : ($value <= $fallback['yellow'] ? 'yellow' : 'red'))
            : ($value >= $fallback['green'] ? 'green' : ($value >= $fallback['yellow'] ? 'yellow' : 'red'));
        $score = $light === 'green' ? 100.0 : ($light === 'yellow' ? 60.0 : 20.0);

        return ['weight' => $weight, 'score' => $score, 'traffic_light' => $light];
    }

    private function defaultRule(string $code): array
    {
        $rules = [
            'UMSATZ_WACHSTUM' => ['green' => 10, 'yellow' => 0, 'lower_is_better' => false, 'weight' => 15],
            'EBITDA_MARGE' => ['green' => 15, 'yellow' => 5, 'lower_is_better' => false, 'weight' => 20],
            'DEBT_EQUITY' => ['green' => 1.0, 'yellow' => 2.5, 'lower_is_better' => true, 'weight' => 15],
            'CURRENT_RATIO' => ['green' => 1.5, 'yellow' => 1.0, 'lower_is_better' => false, 'weight' => 10],
            'RUNWAY' => ['green' => 18, 'yellow' => 6, 'lower_is_better' => false, 'weight' => 10],
            'LTV_CAC' => ['green' => 3.0, 'yellow' => 1.5, 'lower_is_better' => false, 'weight' => 10],
            'EK_QUOTE' => ['green' => 30, 'yellow' => 15, 'lower_is_better' => false, 'weight' => 10],
            'MGMT_SCORE' => ['green' => 7, 'yellow' => 5, 'lower_is_better' => false, 'weight' => 10],
            'MARKET_SCORE' => ['green' => 7, 'yellow' => 5, 'lower_is_better' => false, 'weight' => 10],
        ];

        return $rules[$code] ?? ['green' => 0, 'yellow' => 0, 'lower_is_better' => false, 'weight' => 0];
    }

    public function weightedScore(): float
    {
        $scores = [
            $this->evaluateKpi('UMSATZ_WACHSTUM', $this->umsatzwachstum(), $this->defaultRule('UMSATZ_WACHSTUM')),
            $this->evaluateKpi('EBITDA_MARGE', $this->ebitdaMarge(), $this->defaultRule('EBITDA_MARGE')),
            $this->evaluateKpi('DEBT_EQUITY', $this->debtEquityRatio(), $this->defaultRule('DEBT_EQUITY')),
            $this->evaluateKpi('CURRENT_RATIO', $this->currentRatio(), $this->defaultRule('CURRENT_RATIO')),
            $this->evaluateKpi('RUNWAY', $this->runway(), $this->defaultRule('RUNWAY')),
            $this->evaluateKpi('LTV_CAC', $this->ltvCacRatio(), $this->defaultRule('LTV_CAC')),
            $this->evaluateKpi('MGMT_SCORE', $this->input->mgmt_score, $this->defaultRule('MGMT_SCORE')),
            $this->evaluateKpi('MARKET_SCORE', $this->input->market_score, $this->defaultRule('MARKET_SCORE')),
        ];

        $total = 0;
        foreach ($scores as $s) {
            $total += $s['score'] * ($s['weight'] / 100);
        }

        return round($total, 2);
    }

    public function getRecommendation(float $score): string
    {
        if ($score >= 75) {
            return 'Sehr gut — Finanzierung empfohlen';
        }
        if ($score >= 60) {
            return 'Gut — mit Auflagen finanzierbar';
        }
        if ($score >= 45) {
            return 'Mittelmäßig — kritische KPIs prüfen';
        }
        if ($score >= 30) {
            return 'Schwach — erhebliche Risiken vorhanden';
        }

        return 'Kritisch — Finanzierung nicht empfohlen';
    }

    public function getTrafficLight(float $score): string
    {
        if ($score >= 60) {
            return 'green';
        }
        if ($score >= 40) {
            return 'yellow';
        }

        return 'red';
    }

    // ─────────────────────────────────────────────────────────────
    //  Calculate & Save All KPIs to DB
    // ─────────────────────────────────────────────────────────────

    public function calculateAndSave(Analysis $analysis): array
    {
        $kpis = [
            ['code' => 'UMSATZ_WACHSTUM', 'name' => 'Umsatzwachstum',      'value' => $this->umsatzwachstum(),   'unit' => '%',   'weight' => 15],
            ['code' => 'EBITDA_MARGE',    'name' => 'EBITDA-Marge',         'value' => $this->ebitdaMarge(),      'unit' => '%',   'weight' => 20],
            ['code' => 'DEBT_EQUITY',     'name' => 'Debt/Equity Ratio',    'value' => $this->debtEquityRatio(),  'unit' => 'x',   'weight' => 15],
            ['code' => 'CURRENT_RATIO',   'name' => 'Current Ratio',         'value' => $this->currentRatio(),    'unit' => 'x',   'weight' => 10],
            ['code' => 'RUNWAY',          'name' => 'Runway (Monate)',       'value' => $this->runway(),           'unit' => 'Mo',  'weight' => 10],
            ['code' => 'LTV_CAC',         'name' => 'LTV/CAC Ratio',         'value' => $this->ltvCacRatio(),     'unit' => 'x',   'weight' => 10],
            ['code' => 'EK_QUOTE',        'name' => 'Eigenkapitalquote',     'value' => $this->eigenkapitalQuote(), 'unit' => '%', 'weight' => 10],
            ['code' => 'MGMT_SCORE',      'name' => 'Management-Score',      'value' => $this->input->mgmt_score,  'unit' => '/10', 'weight' => 10],
            ['code' => 'MARKET_SCORE',    'name' => 'Markt & Wettbewerb',    'value' => $this->input->market_score, 'unit' => '/10', 'weight' => 10],
        ];

        $score = $this->weightedScore();

        // Delete old results, re-insert
        $analysis->kpiResults()->delete();

        foreach ($kpis as $kpi) {
            if ($kpi['value'] === null) {
                continue;
            }
            $eval = $this->evaluateKpi($kpi['code'], (float) $kpi['value'], $this->defaultRule($kpi['code']));
            KpiResult::create([
                'analysis_id' => $analysis->id,
                'kpi_code' => $kpi['code'],
                'kpi_name' => $kpi['name'],
                'value' => $kpi['value'],
                'score' => $score,
                'weight' => $eval['weight'],
                'unit' => $kpi['unit'],
                'traffic_light' => $eval['traffic_light'],
            ]);
        }

        $recommendation = $this->getRecommendation($score);
        $analysis->update([
            'total_score' => $score,
            'recommendation' => $recommendation,
            'status' => 'complete',
        ]);

        return [
            'score' => $score,
            'recommendation' => $recommendation,
            'traffic_light' => $this->getTrafficLight($score),
            'kpis' => $kpis,
        ];
    }
}
