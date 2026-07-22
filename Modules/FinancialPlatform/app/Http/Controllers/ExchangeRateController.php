<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FinancialPlatform\Models\ExchangeRate;

class ExchangeRateController extends Controller
{
    public function index(): View
    {
        $rates = ExchangeRate::query()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(25);

        return view('financialplatform::exchange-rates.index', compact('rates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_currency' => 'required|string|size:3',
            'to_currency' => 'required|string|size:3',
            'rate' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        ExchangeRate::updateOrCreate(
            [
                'from_currency' => strtoupper($validated['from_currency']),
                'to_currency' => strtoupper($validated['to_currency']),
                'date' => $validated['date'],
            ],
            ['rate' => $validated['rate']]
        );

        return back()->with('success', __('Exchange rate saved.'));
    }

    public function destroy(ExchangeRate $exchangeRate): RedirectResponse
    {
        $exchangeRate->delete();

        return back()->with('success', __('Exchange rate deleted.'));
    }
}
