<?php

namespace Modules\FinancialPlatform\Services;

use Illuminate\Support\Collection;
use Modules\FinancialPlatform\Models\Analysis;
use Modules\FinancialPlatform\Models\JahresabschlussInput;
use Modules\FinancialPlatform\Models\KpiResult;

class KennzahlenEngine
{
    /** @var Collection<int, JahresabschlussInput> */
    private Collection $years;

    public function __construct(Collection $years)
    {
        // Sorted oldest → newest
        $this->years = $years->sortBy('year_order')->values();
    }

    // ─────────────────────────────────────────────────────────────
    //  KPI Methods (for a single year's data)
    // ─────────────────────────────────────────────────────────────

    /** Eigenkapitalquote in % */
    public function eigenkapitalQuote(JahresabschlussInput $y): ?float
    {
        if (! $y->total_assets || $y->total_assets == 0) {
            return null;
        }

        return ($y->equity / $y->total_assets) * 100;
    }

    /** Quick Ratio (Kasse + Forderungen) / kurzfr. Verbindlichkeiten */
    public function quickRatio(JahresabschlussInput $y): ?float
    {
        if (! $y->current_liabilities || $y->current_liabilities == 0) {
            return null;
        }

        return (($y->cash ?? 0) + ($y->receivables ?? 0)) / $y->current_liabilities;
    }

    /** Current Ratio — Umlaufvermögen / kurzfr. Verbindlichkeiten */
    public function currentRatio(JahresabschlussInput $y): ?float
    {
        if (! $y->current_liabilities || $y->current_liabilities == 0) {
            return null;
        }

        return $y->current_assets / $y->current_liabilities;
    }

    /** ROE — Jahresüberschuss / Eigenkapital in % */
    public function roe(JahresabschlussInput $y): ?float
    {
        if (! $y->equity || $y->equity == 0) {
            return null;
        }

        return ($y->net_profit / $y->equity) * 100;
    }

    /** ROA — Jahresüberschuss / Bilanzsumme in % */
    public function roa(JahresabschlussInput $y): ?float
    {
        if (! $y->total_assets || $y->total_assets == 0) {
            return null;
        }

        return ($y->net_profit / $y->total_assets) * 100;
    }

    /** EBIT-Marge in % */
    public function ebitMarge(JahresabschlussInput $y): ?float
    {
        if (! $y->revenue || $y->revenue == 0) {
            return null;
        }

        return ($y->ebit / $y->revenue) * 100;
    }

    /** Netto-Marge in % */
    public function nettoMarge(JahresabschlussInput $y): ?float
    {
        if (! $y->revenue || $y->revenue == 0) {
            return null;
        }

        return ($y->net_profit / $y->revenue) * 100;
    }

    /** Verschuldungsgrad — Verbindlichkeiten / Eigenkapital */
    public function verschuldungsgrad(JahresabschlussInput $y): ?float
    {
        if (! $y->equity || $y->equity == 0) {
            return null;
        }

        return $y->total_liabilities / $y->equity;
    }

    /** Zinsdeckungsgrad — EBIT / Zinsaufwand */
    public function zinsdeckungsgrad(JahresabschlussInput $y): ?float
    {
        if (! $y->interest_exp || $y->interest_exp == 0) {
            return null;
        }

        return $y->ebit / $y->interest_exp;
    }

    /** DSO — Forderungen × 365 / Umsatz (in Tagen) */
    public function dso(JahresabschlussInput $y): ?float
    {
        if (! $y->revenue || $y->revenue == 0) {
            return null;
        }

        return (($y->receivables ?? 0) * 365) / $y->revenue;
    }

    /** DPO — Verbindlichkeiten aus L+L × 365 / Materialaufwand */
    public function dpo(JahresabschlussInput $y): ?float
    {
        if (! $y->material_costs || $y->material_costs == 0) {
            return null;
        }

        return (($y->payables ?? 0) * 365) / $y->material_costs;
    }

    /** Lagerreichweite — Vorräte × 365 / Materialaufwand */
    public function lagerreichweite(JahresabschlussInput $y): ?float
    {
        if (! $y->material_costs || $y->material_costs == 0) {
            return null;
        }

        return (($y->inventory ?? 0) * 365) / $y->material_costs;
    }

    // ─────────────────────────────────────────────────────────────
    //  Traffic Light
    // ─────────────────────────────────────────────────────────────

    /** Returns green | yellow | red based on KPI code and value */
    public function getAmpel(string $code, float $value): string
    {
        $thresholds = [
            'EK_QUOTE' => ['type' => 'high', 'green' => 30, 'yellow' => 15],
            'QUICK' => ['type' => 'high', 'green' => 1.0, 'yellow' => 0.7],
            'CURRENT' => ['type' => 'high', 'green' => 1.2, 'yellow' => 1.0],
            'ROE' => ['type' => 'high', 'green' => 10, 'yellow' => 5],
            'ROA' => ['type' => 'high', 'green' => 6, 'yellow' => 3],
            'EBIT_MARGE' => ['type' => 'high', 'green' => 7, 'yellow' => 3],
            'NETTO_MARGE' => ['type' => 'high', 'green' => 5, 'yellow' => 2],
            'VERSCHULDUNG' => ['type' => 'low',  'green' => 1.5, 'yellow' => 3.0],
            'ZINSDECKUNG' => ['type' => 'high', 'green' => 3, 'yellow' => 1.5],
            'DSO' => ['type' => 'low',  'green' => 45, 'yellow' => 60],
            'DPO' => ['type' => 'low',  'green' => 30, 'yellow' => 60],
            'LAGER' => ['type' => 'low',  'green' => 40, 'yellow' => 80],
        ];

        if (! isset($thresholds[$code])) {
            return 'yellow';
        }

        $t = $thresholds[$code];
        if ($t['type'] === 'high') {
            if ($value >= $t['green']) {
                return 'green';
            }
            if ($value >= $t['yellow']) {
                return 'yellow';
            }

            return 'red';
        } else {
            if ($value <= $t['green']) {
                return 'green';
            }
            if ($value <= $t['yellow']) {
                return 'yellow';
            }

            return 'red';
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  Trend Arrow (for newest vs previous year)
    // ─────────────────────────────────────────────────────────────
    public function trendArrow(?float $prev, ?float $curr, bool $lowerIsBetter = false): string
    {
        if ($prev === null || $curr === null) {
            return '→';
        }
        $diff = $curr - $prev;
        if (abs($diff) < 0.01) {
            return '→';
        }
        $up = $diff > 0;
        if ($lowerIsBetter) {
            $up = ! $up;
        }

        return $up ? '↑' : '↓';
    }

    // ─────────────────────────────────────────────────────────────
    //  Calculate All KPIs for All Years & Save
    // ─────────────────────────────────────────────────────────────

    public function calculateAndSave(Analysis $analysis): array
    {
        $analysis->kpiResults()->delete();

        $allResults = [];

        foreach ($this->years as $y) {
            $kpis = [
                ['code' => 'EK_QUOTE',    'name' => 'Eigenkapitalquote',  'value' => $this->eigenkapitalQuote($y), 'unit' => '%'],
                ['code' => 'QUICK',       'name' => 'Quick Ratio',         'value' => $this->quickRatio($y),       'unit' => 'x'],
                ['code' => 'CURRENT',     'name' => 'Current Ratio',       'value' => $this->currentRatio($y),     'unit' => 'x'],
                ['code' => 'ROE',         'name' => 'EK-Rendite',          'value' => $this->roe($y),              'unit' => '%'],
                ['code' => 'ROA',         'name' => 'GK-Rendite',          'value' => $this->roa($y),              'unit' => '%'],
                ['code' => 'EBIT_MARGE',  'name' => 'EBIT-Marge',         'value' => $this->ebitMarge($y),        'unit' => '%'],
                ['code' => 'NETTO_MARGE', 'name' => 'Netto-Marge',        'value' => $this->nettoMarge($y),       'unit' => '%'],
                ['code' => 'VERSCHULDUNG', 'name' => 'Verschuldungsgrad',   'value' => $this->verschuldungsgrad($y), 'unit' => 'x'],
                ['code' => 'ZINSDECKUNG', 'name' => 'Zinsdeckungsgrad',   'value' => $this->zinsdeckungsgrad($y), 'unit' => 'x'],
                ['code' => 'DSO',         'name' => 'Debitorenlaufzeit',   'value' => $this->dso($y),             'unit' => 'Tage'],
                ['code' => 'DPO',         'name' => 'Kreditorenlaufzeit',  'value' => $this->dpo($y),             'unit' => 'Tage'],
                ['code' => 'LAGER',       'name' => 'Lagerreichweite',     'value' => $this->lagerreichweite($y), 'unit' => 'Tage'],
            ];

            foreach ($kpis as $kpi) {
                if ($kpi['value'] === null) {
                    continue;
                }
                $light = $this->getAmpel($kpi['code'], $kpi['value']);
                KpiResult::create([
                    'analysis_id' => $analysis->id,
                    'kpi_code' => $kpi['code'],
                    'kpi_name' => $kpi['name'],
                    'value' => $kpi['value'],
                    'traffic_light' => $light,
                    'unit' => $kpi['unit'],
                    'year_label' => $y->year_label,
                ]);
                $allResults[$y->year_label][$kpi['code']] = [
                    'value' => $kpi['value'],
                    'traffic_light' => $light,
                    'unit' => $kpi['unit'],
                ];
            }
        }

        $analysis->update(['status' => 'complete']);

        return $allResults;
    }

    /** Auto-generate Bericht text */
    public function generateBericht(): string
    {
        $newest = $this->years->last();
        if (! $newest) {
            return 'Keine Daten verfügbar.';
        }

        $lines = [];
        $ek = $this->eigenkapitalQuote($newest);
        if ($ek !== null) {
            $amt = $this->getAmpel('EK_QUOTE', $ek);
            $lines[] = 'Die Eigenkapitalquote beträgt '.round($ek, 1).'% und ist damit '.
                ($amt === 'green' ? 'solide' : ($amt === 'yellow' ? 'ausbaufähig' : 'kritisch niedrig')).'.';
        }
        $ebit = $this->ebitMarge($newest);
        if ($ebit !== null) {
            $lines[] = 'Die EBIT-Marge liegt bei '.round($ebit, 1).'%.';
        }
        $z = $this->zinsdeckungsgrad($newest);
        if ($z !== null) {
            $lines[] = 'Der Zinsdeckungsgrad von '.round($z, 2).'x weist auf eine '.
                ($z >= 3 ? 'komfortable' : 'angespannte').' Zinslast hin.';
        }

        return implode(' ', $lines);
    }
}
