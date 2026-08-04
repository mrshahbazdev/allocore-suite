<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialPlatform\Models\Analysis;
use Modules\FinancialPlatform\Models\Company;
use Modules\FinancialPlatform\Models\GmbhInput;
use Modules\FinancialPlatform\Models\ImmobilienInput;
use Modules\FinancialPlatform\Models\JahresabschlussInput;
use Modules\FinancialPlatform\Services\GmbhScoringService;
use Modules\FinancialPlatform\Services\ImmobilienScoringService;
use Modules\FinancialPlatform\Services\KennzahlenEngine;

class ExcelImportController extends Controller
{
    /**
     * Show the import form
     */
    public function show()
    {
        $companies = Company::query()->get();

        return view('financialplatform::import.index', compact('companies'));
    }

    /**
     * Download blank CSV template for a given type
     */
    public function downloadTemplate(string $type)
    {
        [$headers, $exampleRows] = match ($type) {
            'gmbh' => $this->buildGmbhTemplate(),
            'jahresabschluss' => $this->buildJahresabschlussTemplate(),
            'immobilien' => $this->buildImmobilienTemplate(),
            default => abort(404),
        };

        $filename = "allocore-template-{$type}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");

        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        foreach ($exampleRows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    /**
     * Process uploaded CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'type' => 'required|in:gmbh,jahresabschluss,immobilien',
            'company_id' => 'required|exists:financial_companies,id',
            'name' => 'required|string|max:255',
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            $csv = array_map('str_getcsv', file($path));
            $csv = array_filter($csv, fn ($row) => $row !== [''] && ! empty(array_filter($row)));

            $headers = array_shift($csv);
            if (! $headers) {
                throw new \Exception('Keine Header gefunden.');
            }

            $analysis = Analysis::create([
                'company_id' => $request->company_id,
                'user_id' => auth()->id(),
                'type' => $request->type,
                'name' => $request->name,
                'status' => 'draft',
            ]);

            switch ($request->type) {
                case 'gmbh':
                    $this->importGmbh($csv, $headers, $analysis);
                    break;
                case 'jahresabschluss':
                    $this->importJahresabschluss($csv, $headers, $analysis);
                    break;
                case 'immobilien':
                    $this->importImmobilien($csv, $headers, $analysis);
                    break;
            }

            return redirect()->route($request->type.'.show', $analysis)
                ->with('success', 'CSV-Datei erfolgreich importiert und Analyse berechnet.');

        } catch (\Exception $e) {
            return back()->with('error', 'Import fehlgeschlagen: '.$e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  GmbH Import
    // ─────────────────────────────────────────────────────────────

    private function buildGmbhTemplate(): array
    {
        $headers = [
            'revenue_current', 'revenue_prev', 'ebitda',
            'net_profit', 'equity', 'total_debt',
            'total_assets', 'current_assets', 'current_liabilities',
            'cash', 'monthly_burn', 'depreciation',
            'interest', 'cac', 'ltv',
            'mgmt_score', 'market_score',
        ];

        $example = [
            1500000, 1200000, 220000, 120000, 500000, 800000,
            1300000, 600000, 300000, 180000, 30000, 50000,
            20000, 500, 2000, 8, 7,
        ];

        return [$headers, [$example]];
    }

    private function importGmbh(array $rows, array $headers, Analysis $analysis): void
    {
        $firstRow = array_values($rows)[0] ?? null;
        if (! $firstRow) {
            throw new \Exception('Keine Daten gefunden.');
        }

        $data = [];
        foreach ($firstRow as $col => $value) {
            $fieldName = $headers[$col] ?? null;
            if ($fieldName) {
                $data[$fieldName] = is_numeric($value) ? (float) $value : $value;
            }
        }

        $data['analysis_id'] = $analysis->id;
        $input = GmbhInput::create($data);

        $service = new GmbhScoringService($input);
        $service->calculateAndSave($analysis);
    }

    // ─────────────────────────────────────────────────────────────
    //  Jahresabschluss Import
    // ─────────────────────────────────────────────────────────────

    private function buildJahresabschlussTemplate(): array
    {
        $headers = [
            'year_label', 'revenue', 'ebit', 'net_profit',
            'equity', 'total_assets', 'current_assets',
            'cash', 'receivables', 'inventory',
            'current_liabilities', 'total_liabilities',
            'interest_exp', 'material_costs',
            'personnel_costs', 'payables',
        ];

        $examples = [
            ['2022', 1200000, 80000, 50000, 450000, 1100000, 500000, 120000, 220000, 80000, 280000, 650000, 18000, 400000, 320000, 110000],
            ['2023', 1350000, 95000, 65000, 510000, 1200000, 560000, 150000, 250000, 90000, 300000, 690000, 19000, 440000, 340000, 120000],
            ['2024', 1520000, 115000, 80000, 590000, 1350000, 650000, 190000, 290000, 100000, 320000, 760000, 21000, 480000, 370000, 130000],
        ];

        return [$headers, $examples];
    }

    private function importJahresabschluss(array $rows, array $headers, Analysis $analysis): void
    {
        foreach ($rows as $order => $row) {
            if (empty(array_filter($row))) {
                continue;
            }

            $data = ['analysis_id' => $analysis->id, 'year_order' => $order + 1];
            foreach ($row as $col => $value) {
                $field = $headers[$col] ?? null;
                if ($field) {
                    $data[$field] = is_numeric($value) ? (float) $value : $value;
                }
            }
            JahresabschlussInput::create($data);
        }

        $years = $analysis->jahresabschlussInputs()->get();
        $engine = new KennzahlenEngine($years);
        $engine->calculateAndSave($analysis);
    }

    // ─────────────────────────────────────────────────────────────
    //  Immobilien Import
    // ─────────────────────────────────────────────────────────────

    private function buildImmobilienTemplate(): array
    {
        $headers = [
            'purchase_price', 'closing_costs', 'renovation_costs',
            'equity', 'rent_net', 'market_rent',
            'vacancy_rate', 'management_costs_pct',
            'loan_rate', 'repayment_rate', 'loan_term_years',
            'area_sqm', 'location_score', 'condition_score',
            'property_type',
        ];

        $example = [500000, 40000, 0, 150000, 2500, 3000, 5, 10, 3.5, 2.0, 25, 200, 7, 8, 'Mehrfamilienhaus'];

        return [$headers, [$example]];
    }

    private function importImmobilien(array $rows, array $headers, Analysis $analysis): void
    {
        $firstRow = array_values($rows)[0] ?? null;
        if (! $firstRow) {
            throw new \Exception('Keine Daten gefunden.');
        }

        $data = ['analysis_id' => $analysis->id];
        foreach ($firstRow as $col => $value) {
            $field = $headers[$col] ?? null;
            if ($field) {
                $data[$field] = is_numeric($value) ? (float) $value : $value;
            }
        }

        $input = ImmobilienInput::create($data);
        $service = new ImmobilienScoringService($input);
        $service->calculateAndSave($analysis);
    }
}
