<?php

namespace Modules\FinancialPlatform\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialPlatform\Models\KpiThreshold;

class KpiThresholdsSeeder extends Seeder
{
    public function run(): void
    {
        $thresholds = [
            // ─── GmbH Analyse ───────────────────────────────────────────
            ['tool' => 'gmbh', 'kpi_code' => 'UMSATZ_WACHSTUM', 'kpi_name' => 'Umsatzwachstum',    'unit' => '%',   'green_min' => 10,   'yellow_min' => 0,    'lower_is_better' => false, 'weight' => 15],
            ['tool' => 'gmbh', 'kpi_code' => 'EBITDA_MARGE',    'kpi_name' => 'EBITDA-Marge',       'unit' => '%',   'green_min' => 15,   'yellow_min' => 5,    'lower_is_better' => false, 'weight' => 20],
            ['tool' => 'gmbh', 'kpi_code' => 'DEBT_EQUITY',     'kpi_name' => 'Debt/Equity Ratio',  'unit' => 'x',   'green_max' => 1.0,  'yellow_max' => 2.5,  'lower_is_better' => true,  'weight' => 15],
            ['tool' => 'gmbh', 'kpi_code' => 'CURRENT_RATIO',   'kpi_name' => 'Current Ratio',      'unit' => 'x',   'green_min' => 1.5,  'yellow_min' => 1.0,  'lower_is_better' => false, 'weight' => 10],
            ['tool' => 'gmbh', 'kpi_code' => 'RUNWAY',          'kpi_name' => 'Runway (Monate)',    'unit' => 'Mo',  'green_min' => 18,   'yellow_min' => 6,    'lower_is_better' => false, 'weight' => 10],
            ['tool' => 'gmbh', 'kpi_code' => 'LTV_CAC',         'kpi_name' => 'LTV/CAC Ratio',      'unit' => 'x',   'green_min' => 3.0,  'yellow_min' => 1.5,  'lower_is_better' => false, 'weight' => 10],
            ['tool' => 'gmbh', 'kpi_code' => 'MGMT_SCORE',      'kpi_name' => 'Management-Score',   'unit' => '/10', 'green_min' => 7,    'yellow_min' => 5,    'lower_is_better' => false, 'weight' => 10],
            ['tool' => 'gmbh', 'kpi_code' => 'MARKET_SCORE',    'kpi_name' => 'Markt & Wettbewerb', 'unit' => '/10', 'green_min' => 7,    'yellow_min' => 5,    'lower_is_better' => false, 'weight' => 10],

            // ─── Jahresabschluss ────────────────────────────────────────
            ['tool' => 'jahresabschluss', 'kpi_code' => 'EK_QUOTE',    'kpi_name' => 'Eigenkapitalquote',  'unit' => '%',    'green_min' => 30,  'yellow_min' => 15,  'lower_is_better' => false, 'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'QUICK',       'kpi_name' => 'Quick Ratio',        'unit' => 'x',    'green_min' => 1.0, 'yellow_min' => 0.7, 'lower_is_better' => false, 'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'CURRENT',     'kpi_name' => 'Current Ratio',      'unit' => 'x',    'green_min' => 1.2, 'yellow_min' => 1.0, 'lower_is_better' => false, 'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'ROE',         'kpi_name' => 'EK-Rendite',         'unit' => '%',    'green_min' => 10,  'yellow_min' => 5,   'lower_is_better' => false, 'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'ROA',         'kpi_name' => 'GK-Rendite',         'unit' => '%',    'green_min' => 6,   'yellow_min' => 3,   'lower_is_better' => false, 'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'EBIT_MARGE',  'kpi_name' => 'EBIT-Marge',        'unit' => '%',    'green_min' => 7,   'yellow_min' => 3,   'lower_is_better' => false, 'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'NETTO_MARGE', 'kpi_name' => 'Netto-Marge',       'unit' => '%',    'green_min' => 5,   'yellow_min' => 2,   'lower_is_better' => false, 'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'VERSCHULDUNG', 'kpi_name' => 'Verschuldungsgrad',  'unit' => 'x',    'green_max' => 1.5, 'yellow_max' => 3.0, 'lower_is_better' => true,  'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'ZINSDECKUNG', 'kpi_name' => 'Zinsdeckungsgrad',  'unit' => 'x',    'green_min' => 3,   'yellow_min' => 1.5, 'lower_is_better' => false, 'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'DSO',         'kpi_name' => 'Debitorenlaufzeit',  'unit' => 'Tage', 'green_max' => 45,  'yellow_max' => 60,  'lower_is_better' => true,  'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'DPO',         'kpi_name' => 'Kreditorenlaufzeit', 'unit' => 'Tage', 'green_max' => 30,  'yellow_max' => 60,  'lower_is_better' => true,  'weight' => null],
            ['tool' => 'jahresabschluss', 'kpi_code' => 'LAGER',       'kpi_name' => 'Lagerreichweite',    'unit' => 'Tage', 'green_max' => 40,  'yellow_max' => 80,  'lower_is_better' => true,  'weight' => null],

            // ─── Immobilien ─────────────────────────────────────────────
            ['tool' => 'immobilien', 'kpi_code' => 'NETTORENDITE', 'kpi_name' => 'Nettorendite',           'unit' => '%', 'green_min' => 5,    'yellow_min' => 3,   'lower_is_better' => false, 'weight' => 10],
            ['tool' => 'immobilien', 'kpi_code' => 'CF_RENDITE',   'kpi_name' => 'Cashflow-Rendite (EK)',  'unit' => '%', 'green_min' => 8,    'yellow_min' => 3,   'lower_is_better' => false, 'weight' => 20],
            ['tool' => 'immobilien', 'kpi_code' => 'DSCR',         'kpi_name' => 'DSCR',                   'unit' => 'x', 'green_min' => 1.25, 'yellow_min' => 1.0, 'lower_is_better' => false, 'weight' => 20],
            ['tool' => 'immobilien', 'kpi_code' => 'LTV',          'kpi_name' => 'LTV',                    'unit' => '%', 'green_max' => 60,   'yellow_max' => 80,  'lower_is_better' => true,  'weight' => 10],
            ['tool' => 'immobilien', 'kpi_code' => 'MIET_MULTI',   'kpi_name' => 'Mietmultiplikator',      'unit' => 'x', 'green_max' => 20,   'yellow_max' => 28,  'lower_is_better' => true,  'weight' => 10],
            ['tool' => 'immobilien', 'kpi_code' => 'MIETSTEIGERUNG', 'kpi_name' => 'Mietsteigerungspot.',   'unit' => '%', 'green_min' => 15,   'yellow_min' => 5,   'lower_is_better' => false, 'weight' => 15],
        ];

        foreach ($thresholds as $t) {
            KpiThreshold::updateOrCreate(
                ['tool' => $t['tool'], 'kpi_code' => $t['kpi_code']],
                array_merge($t, ['is_active' => true])
            );
        }
    }
}
