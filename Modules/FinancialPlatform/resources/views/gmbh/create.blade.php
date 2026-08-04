@extends('layouts.shell')

@section('title', 'GmbH Analyse erstellen — Allocore')

@section('topbar-actions')
    <a href="{{ route('gmbh.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm hover:border-[#ff9200] hover:text-[#ff9200]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        {{ __('Übersicht') }}
    </a>
@endsection

@section('content')

<form method="POST" action="{{ route('gmbh.store') }}" class="space-y-6">
    @csrf

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_360px]">

        {{-- Left: input fields --}}
        <div class="space-y-6">

            {{-- Basic Info --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="mb-5 flex items-center gap-2 text-lg font-semibold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0094af] text-sm font-bold text-white">1</span>
                    {{ __('Grunddaten') }}
                </h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Name der Analyse') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('z.B. GmbH Bewertung Q1 2024') }}" required
                            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Unternehmen') }} <span class="text-rose-500">*</span></label>
                        <select name="company_id" required class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                            <option value="">{{ __('— Bitte wählen —') }}</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @if($companies->isEmpty())
                            <p class="mt-1 text-xs text-amber-600">
                                {{ __('Erstellen Sie zuerst ein Unternehmen:') }}
                                <a href="{{ route('companies.create') }}" class="underline hover:text-amber-700">{{ __('Unternehmen anlegen') }}</a>
                            </p>
                        @endif
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Analysejahr') }}</label>
                        <input type="text" name="year" value="{{ old('year', date('Y')) }}" placeholder="{{ date('Y') }}"
                            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                </div>
            </section>

            {{-- Revenue --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="mb-5 flex items-center gap-2 text-lg font-semibold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0094af] text-sm font-bold text-white">2</span>
                    {{ __('Umsatz & Ergebnis') }}
                </h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form-field name="revenue_current" label="Umsatz aktuelles Jahr (€)" type="number" step="0.01" placeholder="1500000" required />
                    <x-form-field name="revenue_prev" label="Umsatz Vorjahr (€)" type="number" step="0.01" placeholder="1200000" required />
                    <x-form-field name="ebitda" label="EBITDA (€)" type="number" step="0.01" placeholder="300000" />
                    <x-form-field name="net_profit" label="Jahresüberschuss / Net Profit (€)" type="number" step="0.01" placeholder="150000" />
                    <x-form-field name="depreciation" label="Abschreibungen (€)" type="number" step="0.01" placeholder="50000" />
                    <x-form-field name="interest" label="Zinsaufwand (€)" type="number" step="0.01" placeholder="20000" />
                </div>
            </section>

            {{-- Balance Sheet --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="mb-5 flex items-center gap-2 text-lg font-semibold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0094af] text-sm font-bold text-white">3</span>
                    {{ __('Bilanz') }}
                </h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form-field name="equity" label="Eigenkapital (€)" type="number" step="0.01" placeholder="500000" required />
                    <x-form-field name="total_debt" label="Gesamtverbindlichkeiten (€)" type="number" step="0.01" placeholder="800000" />
                    <x-form-field name="total_assets" label="Bilanzsumme (€)" type="number" step="0.01" placeholder="1300000" />
                    <x-form-field name="current_assets" label="Umlaufvermögen (€)" type="number" step="0.01" placeholder="600000" />
                    <x-form-field name="current_liabilities" label="Kurzfr. Verbindlichkeiten (€)" type="number" step="0.01" placeholder="300000" />
                    <x-form-field name="cash" label="Kassenbestand / Cash (€)" type="number" step="0.01" placeholder="180000" />
                    <x-form-field name="monthly_burn" label="Monatlicher Cashburn (€)" type="number" step="0.01" placeholder="30000" helper="Für Runway-Berechnung" />
                </div>
            </section>

            {{-- Customer Metrics --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="mb-5 flex items-center gap-2 text-lg font-semibold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0094af] text-sm font-bold text-white">4</span>
                    {{ __('Kundenmetriken (Optional)') }}
                </h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form-field name="cac" label="CAC — Customer Acquisition Cost (€)" type="number" step="0.01" placeholder="500" />
                    <x-form-field name="ltv" label="LTV — Lifetime Value (€)" type="number" step="0.01" placeholder="2000" />
                </div>
            </section>

        </div>

        {{-- Right: qualitative scores & weights --}}
        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="mb-5 flex items-center gap-2 text-lg font-semibold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#ff9200] text-sm font-bold text-white">5</span>
                    {{ __('Qualitative Bewertung') }}
                </h2>

                <div class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">{{ __('Management-Qualität (1–10)') }} <span class="text-rose-500">*</span></label>
                        <input type="range" name="mgmt_score" id="mgmt_score" min="1" max="10" step="1" value="{{ old('mgmt_score', 7) }}"
                            class="w-full accent-[#ff9200]" oninput="document.getElementById('mgmt_val').textContent = this.value">
                        <div class="mt-1 flex justify-between text-xs text-slate-500">
                            <span>{{ __('1 — Kritisch') }}</span>
                            <span id="mgmt_val" class="text-base font-bold text-[#ff9200]">{{ old('mgmt_score', 7) }}</span>
                            <span>{{ __('10 — Exzellent') }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">{{ __('Markt & Wettbewerb (1–10)') }} <span class="text-rose-500">*</span></label>
                        <input type="range" name="market_score" id="market_score" min="1" max="10" step="1" value="{{ old('market_score', 7) }}"
                            class="w-full accent-[#ff9200]" oninput="document.getElementById('market_val').textContent = this.value">
                        <div class="mt-1 flex justify-between text-xs text-slate-500">
                            <span>{{ __('1 — Schwach') }}</span>
                            <span id="market_val" class="text-base font-bold text-[#ff9200]">{{ old('market_score', 7) }}</span>
                            <span>{{ __('10 — Dominant') }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="mb-2 text-base font-semibold text-slate-900">{{ __('Gewichtung der KPIs') }}</h2>
                <p class="mb-4 text-xs text-slate-500">{{ __('Prozentanteile je KPI (0–100). Die Summe darf 100% nicht überschreiten.') }}</p>
                <div class="space-y-3">
                    @foreach([
                        ['EBITDA_MARGE', 'EBITDA-Marge', 20],
                        ['UMSATZ_WACHSTUM', 'Umsatzwachstum', 15],
                        ['DEBT_EQUITY', 'Debt/Equity', 15],
                        ['CURRENT_RATIO', 'Current Ratio', 10],
                        ['RUNWAY', 'Runway', 10],
                        ['LTV_CAC', 'LTV/CAC', 10],
                        ['EK_QUOTE', 'Eigenkapitalquote', 10],
                        ['MGMT_SCORE', 'Management', 10],
                        ['MARKET_SCORE', 'Markt', 10],
                    ] as [$code, $name, $defaultWeight])
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <label for="weight_{{ $code }}" class="text-slate-600">{{ $name }}</label>
                            <div class="flex items-center gap-2">
                                <input id="weight_{{ $code }}" type="number" min="0" max="100" step="1" name="weights[{{ $code }}]"
                                    value="{{ old('weights.' . $code, $defaultWeight) }}"
                                    class="w-20 rounded-lg border-slate-300 py-1.5 text-center text-sm font-semibold text-slate-700 focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <span class="text-xs text-slate-400">%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <button type="submit" class="w-full rounded-xl bg-[#ff9200] px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#e68300] focus:outline-none focus:ring-2 focus:ring-[#ff9200] focus:ring-offset-2">
                {{ __('Analyse berechnen & speichern') }}
            </button>

        </aside>

    </div>
</form>

@endsection
