<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\FinancialPlatform\Models\Analysis;
use Modules\FinancialPlatform\Models\Company;
use Modules\FinancialPlatform\Models\JahresabschlussInput;
use Modules\FinancialPlatform\Services\KennzahlenEngine;

class JahresabschlussController extends Controller
{
    public function index()
    {
        $analyses = Analysis::with('company')

            ->where('type', 'jahresabschluss')
            ->latest()
            ->paginate(10);

        return view('financialplatform::jahresabschluss.index', compact('analyses'));
    }

    public function create()
    {
        $companies = Company::query()->get();

        return view('financialplatform::jahresabschluss.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:financial_companies,id',
            'name' => 'required|string|max:255',
            'years' => 'required|array|min:1|max:3',
            'years.*.year_label' => 'required|string|max:10',
            'years.*.revenue' => 'nullable|numeric',
            'years.*.ebit' => 'nullable|numeric',
            'years.*.total_assets' => 'nullable|numeric',
            'years.*.equity' => 'nullable|numeric',
        ]);

        $analysis = Analysis::create([
            'company_id' => $request->company_id,
            'user_id' => auth()->id(),
            'type' => 'jahresabschluss',
            'name' => $request->name,
            'status' => 'draft',
        ]);

        // Save each year's data
        foreach ($request->years as $order => $yearData) {
            JahresabschlussInput::create(array_merge($yearData, [
                'analysis_id' => $analysis->id,
                'year_order' => $order + 1,
            ]));
        }

        // Run KennzahlenEngine
        $years = $analysis->jahresabschlussInputs;
        $engine = new KennzahlenEngine($years);
        $engine->calculateAndSave($analysis);

        return redirect()->route('jahresabschluss.show', $analysis)
            ->with('success', 'Jahresabschluss-Analyse erfolgreich erstellt.');
    }

    public function show(Analysis $jahresabschluss)
    {
        $jahresabschluss->load(['company', 'jahresabschlussInputs', 'kpiResults']);

        $years = $jahresabschluss->jahresabschlussInputs;
        $engine = new KennzahlenEngine($years);
        $bericht = $engine->generateBericht();

        return view('financialplatform::jahresabschluss.show', [
            'analysis' => $jahresabschluss,
            'bericht' => $bericht,
        ]);
    }

    public function destroy(Analysis $jahresabschluss)
    {
        $jahresabschluss->delete();

        return redirect()->route('jahresabschluss.index')
            ->with('success', 'Analyse gelöscht.');
    }

    public function exportPdf(Analysis $jahresabschluss)
    {
        $jahresabschluss->load(['company', 'jahresabschlussInputs', 'kpiResults']);

        $years = $jahresabschluss->jahresabschlussInputs;
        $engine = new KennzahlenEngine($years);
        $bericht = $engine->generateBericht();

        $pdf = Pdf::loadView('jahresabschluss.pdf', [
            'analysis' => $jahresabschluss,
            'bericht' => $bericht,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('jahresabschluss-'.$jahresabschluss->id.'.pdf');
    }
}
