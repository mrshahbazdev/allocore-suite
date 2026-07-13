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
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
     * Download blank template for a given type
     */
    public function downloadTemplate(string $type)
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        match ($type) {
            'gmbh' => $this->buildGmbhTemplate($sheet),
            'jahresabschluss' => $this->buildJahresabschlussTemplate($sheet),
            'immobilien' => $this->buildImmobilienTemplate($sheet),
            default => abort(404),
        };

        // Style header row
        $sheet->getStyle('A1:Z1')->applyFromArray([
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '312E81']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        ]);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = "allocore-template-{$type}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        $writer->save('php://output');
        exit;
    }

    /**
     * Process uploaded Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'type' => 'required|in:gmbh,jahresabschluss,immobilien',
            'company_id' => 'required|exists:financial_companies,id',
            'name' => 'required|string|max:255',
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            // Remove header row
            $headers = array_shift($rows);

            $analysis = Analysis::create([
                'company_id' => $request->company_id,
                'user_id' => auth()->id(),
                'type' => $request->type,
                'name' => $request->name,
                'status' => 'draft',
            ]);

            switch ($request->type) {
                case 'gmbh':
                    $this->importGmbh($rows, $headers, $analysis);
                    break;
                case 'jahresabschluss':
                    $this->importJahresabschluss($rows, $headers, $analysis);
                    break;
                case 'immobilien':
                    $this->importImmobilien($rows, $headers, $analysis);
                    break;
            }

            return redirect()->route($request->type.'.show', $analysis)
                ->with('success', 'Excel-Datei erfolgreich importiert und Analyse berechnet.');

        } catch (\Exception $e) {
            return back()->with('error', 'Import fehlgeschlagen: '.$e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  GmbH Import
    // ─────────────────────────────────────────────────────────────

    private function buildGmbhTemplate($sheet): void
    {
        $headers = [
            'A' => 'revenue_current', 'B' => 'revenue_prev', 'C' => 'ebitda',
            'D' => 'net_profit', 'E' => 'equity', 'F' => 'total_debt',
            'G' => 'total_assets', 'H' => 'current_assets', 'I' => 'current_liabilities',
            'J' => 'cash', 'K' => 'monthly_burn', 'L' => 'depreciation',
            'M' => 'interest', 'N' => 'cac', 'O' => 'ltv',
            'P' => 'mgmt_score', 'Q' => 'market_score',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
            $sheet->getColumnDimension($col)->setWidth(18);
        }
        // Example row
        $sheet->fromArray([
            1500000, 1200000, 220000, 120000, 500000, 800000,
            1300000, 600000, 300000, 180000, 30000, 50000,
            20000, 500, 2000, 8, 7,
        ], null, 'A2');
    }

    private function importGmbh(array $rows, array $headers, Analysis $analysis): void
    {
        $firstRow = array_values($rows)[0] ?? null;
        if (! $firstRow) {
            throw new \Exception('Keine Daten gefunden.');
        }

        // Map: header letter → field name
        $headerMap = array_flip($headers);

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

    private function buildJahresabschlussTemplate($sheet): void
    {
        $headers = [
            'A' => 'year_label', 'B' => 'revenue', 'C' => 'ebit', 'D' => 'net_profit',
            'E' => 'equity', 'F' => 'total_assets', 'G' => 'current_assets',
            'H' => 'cash', 'I' => 'receivables', 'J' => 'inventory',
            'K' => 'current_liabilities', 'L' => 'total_liabilities',
            'M' => 'interest_exp', 'N' => 'material_costs',
            'O' => 'personnel_costs', 'P' => 'payables',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
            $sheet->getColumnDimension($col)->setWidth(16);
        }
        // 3 example years
        $sheet->fromArray(['2022', 1200000, 80000, 50000, 450000, 1100000, 500000, 120000, 220000, 80000, 280000, 650000, 18000, 400000, 320000, 110000], null, 'A2');
        $sheet->fromArray(['2023', 1350000, 95000, 65000, 510000, 1200000, 560000, 150000, 250000, 90000, 300000, 690000, 19000, 440000, 340000, 120000], null, 'A3');
        $sheet->fromArray(['2024', 1520000, 115000, 80000, 590000, 1350000, 650000, 190000, 290000, 100000, 320000, 760000, 21000, 480000, 370000, 130000], null, 'A4');
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

    private function buildImmobilienTemplate($sheet): void
    {
        $headers = [
            'A' => 'purchase_price', 'B' => 'closing_costs', 'C' => 'renovation_costs',
            'D' => 'equity', 'E' => 'rent_net', 'F' => 'market_rent',
            'G' => 'vacancy_rate', 'H' => 'management_costs_pct',
            'I' => 'loan_rate', 'J' => 'repayment_rate', 'K' => 'loan_term_years',
            'L' => 'area_sqm', 'M' => 'location_score', 'N' => 'condition_score',
            'O' => 'property_type',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
            $sheet->getColumnDimension($col)->setWidth(18);
        }
        $sheet->fromArray([500000, 40000, 0, 150000, 2500, 3000, 5, 10, 3.5, 2.0, 25, 200, 7, 8, 'Mehrfamilienhaus'], null, 'A2');
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
