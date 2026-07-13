<?php

namespace Modules\FinancialPlatform\Services;

use Modules\FinancialPlatform\Models\Analysis;
use Modules\FinancialPlatform\Models\ImmobilienInput;
use Modules\FinancialPlatform\Models\KpiResult;

class ImmobilienScoringService
{
    private ImmobilienInput $input;

    /** @var array<string, float> */
    private array $customWeights = [];

    public function __construct(ImmobilienInput $input)
    {
        $this->input = $input;
        $this->customWeights = collect($this->input->custom_weights ?? [])
            ->mapWithKeys(fn ($value, $code) => [(string) $code => (float) $value])
            ->all();
    }

    // ─────────────────────────────────────────────────────────────
    //  Derived Values
    // ─────────────────────────────────────────────────────────────

    /** Gesamtinvestition = Kaufpreis + Nebenkosten + Renovierung */
    public function gesamtinvestition(): float
    {
        return (float) $this->input->purchase_price
             + (float) $this->input->closing_costs
             + (float) $this->input->renovation_costs;
    }

    /** Fremdkapital (Darlehen) = Gesamtinvestition - Eigenkapital */
    public function darlehen(): float
    {
        return max(0, $this->gesamtinvestition() - (float) $this->input->equity);
    }

    /** Jahresbruttomiete */
    public function jahresbruttomiete(): float
    {
        return (float) $this->input->rent_net * 12;
    }

    /** NOI = Bruttomiete × (1 - Leerstand%) - Bewirtschaftungskosten */
    public function noi(): float
    {
        $brutto = $this->jahresbruttomiete();
        $netAfterVacancy = $brutto * (1 - ((float) $this->input->vacancy_rate / 100));
        $mgmtCosts = $netAfterVacancy * ((float) $this->input->management_costs_pct / 100);

        return $netAfterVacancy - $mgmtCosts;
    }

    /** Jährlicher Schuldendienst (Annuität) */
    public function schuldendienst(): float
    {
        $darlehen = $this->darlehen();
        if ($darlehen <= 0) {
            return 0;
        }
        $rate = ((float) $this->input->loan_rate + (float) $this->input->repayment_rate) / 100;

        return $darlehen * $rate;
    }

    /** Cashflow = NOI - Schuldendienst */
    public function cashflow(): float
    {
        return $this->noi() - $this->schuldendienst();
    }

    /** Nettorendite = NOI / Gesamtinvestition × 100 */
    public function nettorendite(): ?float
    {
        $gi = $this->gesamtinvestition();
        if ($gi == 0) {
            return null;
        }

        return ($this->noi() / $gi) * 100;
    }

    /** Cashflow-auf-Eigenkapital-Rendite */
    public function cashflowRendite(): ?float
    {
        $eq = (float) $this->input->equity;
        if ($eq == 0) {
            return null;
        }

        return ($this->cashflow() / $eq) * 100;
    }

    /** DSCR = NOI / Schuldendienst */
    public function dscr(): ?float
    {
        $sd = $this->schuldendienst();
        if ($sd == 0) {
            return null;
        }

        return $this->noi() / $sd;
    }

    /** LTV = Darlehen / Kaufpreis × 100 */
    public function ltv(): ?float
    {
        $kp = (float) $this->input->purchase_price;
        if ($kp == 0) {
            return null;
        }

        return ($this->darlehen() / $kp) * 100;
    }

    /** Mietmultiplikator = Kaufpreis / Jahresbruttomiete */
    public function mietMultiplikator(): ?float
    {
        $miete = $this->jahresbruttomiete();
        if ($miete == 0) {
            return null;
        }

        return (float) $this->input->purchase_price / $miete;
    }

    /** Mietpreis pro qm */
    public function mietpreisQm(): ?float
    {
        $sqm = (float) $this->input->area_sqm;
        if ($sqm == 0) {
            return null;
        }

        return (float) $this->input->rent_net / $sqm;
    }

    /** Mietsteigerungspotenzial in % (Marktmiete vs. Istmiete) */
    public function mietsteigerungspotenzial(): ?float
    {
        $current = (float) $this->input->rent_net;
        if ($current == 0) {
            return null;
        }
        $market = (float) $this->input->market_rent;

        return (($market - $current) / $current) * 100;
    }

    // ─────────────────────────────────────────────────────────────
    //  Scoring Engine
    // ─────────────────────────────────────────────────────────────

    private function scoreHigh(float $v, float $green, float $yellow): float
    {
        if ($v >= $green) {
            return 100.0;
        }
        if ($v >= $yellow) {
            return 60.0;
        }

        return 20.0;
    }

    private function scoreLow(float $v, float $green, float $yellow): float
    {
        if ($v <= $green) {
            return 100.0;
        }
        if ($v <= $yellow) {
            return 60.0;
        }

        return 20.0;
    }

    private function scoreScale(int $v): float
    {
        // 1-10 scale → green ≥ 7, yellow ≥ 5
        if ($v >= 7) {
            return 100.0;
        }
        if ($v >= 5) {
            return 60.0;
        }

        return 20.0;
    }

    public function weightedScore(): float
    {
        $scores = [];

        // Cashflow p.a. (10) — green >= 0, yellow >= -5000
        $v = $this->cashflow();
        $scores[] = ['w' => $this->weight('CASHFLOW', 10), 's' => $this->scoreHigh($v, 0, -5000)];

        // Nettorendite (10%) — green ≥ 5%, yellow ≥ 3%
        $v = $this->nettorendite();
        $scores[] = ['w' => $this->weight('NETTORENDITE', 10), 's' => $v !== null ? $this->scoreHigh($v, 5, 3) : 50];

        // Cashflow-Rendite (20%) — green ≥ 8%, yellow ≥ 3%
        $v = $this->cashflowRendite();
        $scores[] = ['w' => $this->weight('CF_RENDITE', 20), 's' => $v !== null ? $this->scoreHigh($v, 8, 3) : 50];

        // DSCR (20%) — green ≥ 1.25, yellow ≥ 1.0
        $v = $this->dscr();
        $scores[] = ['w' => $this->weight('DSCR', 20), 's' => $v !== null ? $this->scoreHigh($v, 1.25, 1.0) : 50];

        // LTV (10%, lower is better) — green ≤ 60%, yellow ≤ 80%
        $v = $this->ltv();
        $scores[] = ['w' => $this->weight('LTV', 10), 's' => $v !== null ? $this->scoreLow($v, 60, 80) : 50];

        // Lage-Score (10%)
        $v = $this->input->location_score;
        $scores[] = ['w' => $this->weight('LOCATION_SCORE', 10), 's' => $v !== null ? $this->scoreScale($v) : 50];

        // Zustand-Score (5%)
        $v = $this->input->condition_score;
        $scores[] = ['w' => $this->weight('CONDITION_SCORE', 5), 's' => $v !== null ? $this->scoreScale($v) : 50];

        // Mietmultiplikator (10%, lower is better) — green ≤ 20, yellow ≤ 28
        $v = $this->mietMultiplikator();
        $scores[] = ['w' => $this->weight('MIET_MULTI', 10), 's' => $v !== null ? $this->scoreLow($v, 20, 28) : 50];

        // Mietsteigerungspotenzial (15%) — green ≥ 15%, yellow ≥ 5%
        $v = $this->mietsteigerungspotenzial();
        $scores[] = ['w' => $this->weight('MIETSTEIGERUNG', 15), 's' => $v !== null ? $this->scoreHigh($v, 15, 5) : 50];

        $total = 0;
        foreach ($scores as $s) {
            $total += $s['s'] * ($s['w'] / 100);
        }

        return round($total, 2);
    }

    private function weight(string $code, float $default): float
    {
        return array_key_exists($code, $this->customWeights) ? $this->customWeights[$code] : $default;
    }

    public function getRecommendation(float $score): string
    {
        if ($score >= 75) {
            return 'Sehr gutes Investment — klare Kaufempfehlung';
        }
        if ($score >= 60) {
            return 'Solides Investment — mit Auflagen empfehlenswert';
        }
        if ($score >= 45) {
            return 'Durchschnittlich — kritische KPIs überprüfen';
        }
        if ($score >= 30) {
            return 'Risikobehaftet — weitere Due Diligence erforderlich';
        }

        return 'Nicht empfohlen — schlechtes Rendite/Risiko-Verhältnis';
    }

    // ─────────────────────────────────────────────────────────────
    //  Calculate & Save
    // ─────────────────────────────────────────────────────────────

    public function calculateAndSave(Analysis $analysis): array
    {
        $score = $this->weightedScore();
        $light = $score >= 60 ? 'green' : ($score >= 40 ? 'yellow' : 'red');

        $kpis = [
            ['code' => 'GESAMTINVEST',   'name' => 'Gesamtinvestition',        'value' => $this->gesamtinvestition(),    'unit' => 'EUR', 'weight' => 0],
            ['code' => 'NOI',            'name' => 'NOI (Nettobetriebsertrag)', 'value' => $this->noi(),                  'unit' => 'EUR', 'weight' => 0],
            ['code' => 'CASHFLOW',       'name' => 'Cashflow p.a.',             'value' => $this->cashflow(),             'unit' => 'EUR', 'weight' => $this->weight('CASHFLOW', 10)],
            ['code' => 'NETTORENDITE',   'name' => 'Nettorendite',              'value' => $this->nettorendite(),         'unit' => '%',   'weight' => $this->weight('NETTORENDITE', 10)],
            ['code' => 'CF_RENDITE',     'name' => 'Cashflow-Rendite (EK)',     'value' => $this->cashflowRendite(),      'unit' => '%',   'weight' => $this->weight('CF_RENDITE', 20)],
            ['code' => 'DSCR',           'name' => 'DSCR',                      'value' => $this->dscr(),                 'unit' => 'x',   'weight' => $this->weight('DSCR', 20)],
            ['code' => 'LTV',            'name' => 'LTV',                        'value' => $this->ltv(),                  'unit' => '%',   'weight' => $this->weight('LTV', 10)],
            ['code' => 'MIET_MULTI',     'name' => 'Mietmultiplikator',         'value' => $this->mietMultiplikator(),    'unit' => 'x',   'weight' => $this->weight('MIET_MULTI', 10)],
            ['code' => 'MIETSTEIGERUNG', 'name' => 'Mietsteigerungspotenzial', 'value' => $this->mietsteigerungspotenzial(), 'unit' => '%', 'weight' => $this->weight('MIETSTEIGERUNG', 15)],
        ];

        $analysis->kpiResults()->delete();
        foreach ($kpis as $kpi) {
            if ($kpi['value'] === null) {
                continue;
            }
            KpiResult::create([
                'analysis_id' => $analysis->id,
                'kpi_code' => $kpi['code'],
                'kpi_name' => $kpi['name'],
                'value' => $kpi['value'],
                'score' => $score,
                'weight' => $kpi['weight'],
                'unit' => $kpi['unit'],
                'traffic_light' => $light,
            ]);
        }

        $recommendation = $this->getRecommendation($score);
        $analysis->update([
            'total_score' => $score,
            'recommendation' => $recommendation,
            'status' => 'complete',
        ]);

        return compact('score', 'light', 'recommendation', 'kpis');
    }
}
