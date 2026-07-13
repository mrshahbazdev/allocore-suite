<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\FinancialPlatform\Models\Analysis;
use Modules\FinancialPlatform\Models\Company;
use Modules\FinancialPlatform\Models\GmbhInput;
use Modules\FinancialPlatform\Services\GmbhScoringService;

class GmbhAnalyseController extends Controller
{
    private const WEIGHT_CODES = [
        'UMSATZ_WACHSTUM',
        'EBITDA_MARGE',
        'DEBT_EQUITY',
        'CURRENT_RATIO',
        'RUNWAY',
        'LTV_CAC',
        'EK_QUOTE',
        'MGMT_SCORE',
        'MARKET_SCORE',
    ];

    public function index()
    {
        $analyses = Analysis::with('company')

            ->where('type', 'gmbh')
            ->latest()
            ->paginate(10);

        return view('financialplatform::gmbh.index', compact('analyses'));
    }

    public function create()
    {
        $companies = Company::query()->get();

        return view('financialplatform::gmbh.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:financial_companies,id',
            'name' => 'required|string|max:255',
            'revenue_current' => 'required|numeric|min:0',
            'revenue_prev' => 'required|numeric|min:0',
            'equity' => 'required|numeric',
            'total_debt' => 'nullable|numeric|min:0',
            'current_assets' => 'nullable|numeric|min:0',
            'current_liabilities' => 'nullable|numeric|min:0',
            'cash' => 'nullable|numeric|min:0',
            'monthly_burn' => 'nullable|numeric|min:0',
            'depreciation' => 'nullable|numeric|min:0',
            'interest' => 'nullable|numeric|min:0',
            'net_profit' => 'nullable|numeric',
            'cac' => 'nullable|numeric|min:0',
            'ltv' => 'nullable|numeric|min:0',
            'mgmt_score' => 'nullable|integer|min:1|max:10',
            'market_score' => 'nullable|integer|min:1|max:10',
            'weights' => 'nullable|array',
            'weights.*' => 'nullable|numeric|min:0|max:100',
        ]);
        $normalizedWeights = $this->normalizeWeights($request->input('weights', []));
        if (array_sum($normalizedWeights) > 100) {
            return back()
                ->withErrors(['weights' => 'Die Summe der KPI-Gewichte darf 100% nicht ueberschreiten.'])
                ->withInput();
        }

        // Create parent analysis
        $analysis = Analysis::create([
            'company_id' => $request->company_id,
            'user_id' => auth()->id(),
            'type' => 'gmbh',
            'name' => $request->name,
            'status' => 'draft',
        ]);

        // Create input record
        $inputData = $request->except(['company_id', 'name', '_token', 'weights']);
        $inputData['custom_weights'] = $normalizedWeights;
        $input = GmbhInput::create(array_merge($inputData, ['analysis_id' => $analysis->id]));

        // Run scoring
        $service = new GmbhScoringService($input);
        $service->calculateAndSave($analysis);

        return redirect()->route('gmbh.show', $analysis)
            ->with('success', 'GmbH Analyse wurde erfolgreich erstellt und berechnet.');
    }

    public function show(Analysis $gmbh)
    {
        $gmbh->load(['company', 'gmbhInput', 'kpiResults']);

        return view('financialplatform::gmbh.show', ['analysis' => $gmbh]);
    }

    public function edit(Analysis $gmbh)
    {
        $companies = Company::query()->get();
        $gmbh->load('gmbhInput');

        return view('financialplatform::gmbh.edit', ['analysis' => $gmbh, 'companies' => $companies]);
    }

    public function update(Request $request, Analysis $gmbh)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'revenue_current' => 'required|numeric|min:0',
            'revenue_prev' => 'required|numeric|min:0',
            'equity' => 'required|numeric',
            'weights' => 'nullable|array',
            'weights.*' => 'nullable|numeric|min:0|max:100',
        ]);
        $normalizedWeights = $this->normalizeWeights($request->input('weights', []));
        if (array_sum($normalizedWeights) > 100) {
            return back()
                ->withErrors(['weights' => 'Die Summe der KPI-Gewichte darf 100% nicht ueberschreiten.'])
                ->withInput();
        }

        $gmbh->update(['name' => $request->name]);

        $inputData = $request->except(['name', '_token', '_method', 'weights']);
        $inputData['custom_weights'] = $normalizedWeights;
        $gmbh->gmbhInput->update($inputData);

        // Re-calculate
        $service = new GmbhScoringService($gmbh->gmbhInput->fresh());
        $service->calculateAndSave($gmbh->fresh());

        return redirect()->route('gmbh.show', $gmbh)
            ->with('success', 'Analyse aktualisiert und neu berechnet.');
    }

    public function destroy(Analysis $gmbh)
    {
        $gmbh->delete();

        return redirect()->route('gmbh.index')
            ->with('success', 'Analyse gelöscht.');
    }

    /** PDF Export */
    public function exportPdf(Analysis $gmbh)
    {
        $gmbh->load(['company', 'gmbhInput', 'kpiResults']);

        $pdf = Pdf::loadView('gmbh.pdf', ['analysis' => $gmbh])
            ->setPaper('a4', 'portrait');

        return $pdf->download('gmbh-analyse-'.$gmbh->id.'.pdf');
    }

    private function normalizeWeights(array $weights): array
    {
        $normalized = [];
        foreach (self::WEIGHT_CODES as $code) {
            if (! array_key_exists($code, $weights)) {
                continue;
            }
            $value = (float) $weights[$code];
            $normalized[$code] = round(max(0, min(100, $value)), 2);
        }

        return $normalized;
    }
}
