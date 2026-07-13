<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\FinancialPlatform\Models\Analysis;
use Modules\FinancialPlatform\Models\Company;
use Modules\FinancialPlatform\Models\ImmobilienInput;
use Modules\FinancialPlatform\Services\ImmobilienScoringService;

class ImmobilienController extends Controller
{
    private const WEIGHT_CODES = [
        'CASHFLOW',
        'NETTORENDITE',
        'CF_RENDITE',
        'DSCR',
        'LTV',
        'MIET_MULTI',
        'MIETSTEIGERUNG',
        'LOCATION_SCORE',
        'CONDITION_SCORE',
    ];

    public function index()
    {
        $analyses = Analysis::with('company')

            ->where('type', 'immobilien')
            ->latest()
            ->paginate(10);

        return view('financialplatform::immobilien.index', compact('analyses'));
    }

    public function create()
    {
        $companies = Company::query()->get();

        return view('financialplatform::immobilien.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:financial_companies,id',
            'name' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:1',
            'closing_costs' => 'required|numeric|min:0',
            'equity' => 'required|numeric|min:0',
            'rent_net' => 'required|numeric|min:0',
            'loan_rate' => 'required|numeric|min:0|max:20',
            'repayment_rate' => 'required|numeric|min:0|max:20',
            'location_score' => 'required|integer|min:1|max:10',
            'condition_score' => 'required|integer|min:1|max:10',
            'weights' => 'nullable|array',
            'weights.*' => 'nullable|numeric|min:0|max:100',
        ]);
        $normalizedWeights = $this->normalizeWeights($request->input('weights', []));
        if (array_sum($normalizedWeights) > 100) {
            return back()
                ->withErrors(['weights' => 'Die Summe der KPI-Gewichte darf 100% nicht ueberschreiten.'])
                ->withInput();
        }

        $analysis = Analysis::create([
            'company_id' => $request->company_id,
            'user_id' => auth()->id(),
            'type' => 'immobilien',
            'name' => $request->name,
            'status' => 'draft',
        ]);

        $inputData = $request->except(['company_id', 'name', '_token', 'weights']);
        $inputData['custom_weights'] = $normalizedWeights;
        $input = ImmobilienInput::create(array_merge($inputData, ['analysis_id' => $analysis->id]));

        $service = new ImmobilienScoringService($input);
        $service->calculateAndSave($analysis);

        return redirect()->route('immobilien.show', $analysis)
            ->with('success', 'Immobilienanalyse erfolgreich erstellt.');
    }

    public function show(Analysis $immobilien)
    {
        $immobilien->load(['company', 'immobilienInput', 'kpiResults']);

        $service = new ImmobilienScoringService($immobilien->immobilienInput);
        $derived = [
            'gesamtinvestition' => $service->gesamtinvestition(),
            'darlehen' => $service->darlehen(),
            'noi' => $service->noi(),
            'cashflow' => $service->cashflow(),
            'schuldendienst' => $service->schuldendienst(),
        ];

        return view('financialplatform::immobilien.show', [
            'analysis' => $immobilien,
            'derived' => $derived,
        ]);
    }

    public function destroy(Analysis $immobilien)
    {
        $immobilien->delete();

        return redirect()->route('immobilien.index')
            ->with('success', 'Analyse gelöscht.');
    }

    /** Compare multiple properties */
    public function compare(Request $request)
    {
        $ids = $request->input('ids', []);
        $analyses = Analysis::with(['immobilienInput', 'kpiResults', 'company'])

            ->where('type', 'immobilien')
            ->whereIn('id', $ids)
            ->get();

        return view('immobilien.compare', compact('analyses'));
    }

    public function exportPdf(Analysis $immobilien)
    {
        $immobilien->load(['company', 'immobilienInput', 'kpiResults']);

        $service = new ImmobilienScoringService($immobilien->immobilienInput);
        $derived = [
            'gesamtinvestition' => $service->gesamtinvestition(),
            'darlehen' => $service->darlehen(),
            'noi' => $service->noi(),
            'cashflow' => $service->cashflow(),
            'schuldendienst' => $service->schuldendienst(),
        ];

        $pdf = Pdf::loadView('immobilien.pdf', [
            'analysis' => $immobilien,
            'derived' => $derived,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('immobilien-analyse-'.$immobilien->id.'.pdf');
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
