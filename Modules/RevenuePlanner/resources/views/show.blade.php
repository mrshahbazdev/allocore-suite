@php $title = $plan->name . ' — ' . __('Umsatzplan'); @endphp
@extends('layouts.shell')

@section('content')
    @include('revenueplanner::partials.nav')

    <div class="space-y-8" data-no-navigate>
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <!-- Executive Summary Banner -->
        <div class="rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-8 text-white shadow-lg space-y-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b border-white/10 pb-6">
                <div>
                    <span class="rounded-full bg-cyan-400/20 px-3 py-1 text-xs font-bold text-cyan-300 uppercase">
                        {{ $plan->fiscal_year }} {{ __('Umsatz-Strategie') }}
                    </span>
                    <h1 class="mt-2 text-3xl font-black">{{ $plan->name }}</h1>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ __('Berechneter Zielumsatz:') }} <strong>{{ number_format($plan->target_revenue_annual, 0, ',', '.') }} €</strong> | 
                        {{ __('Ist-Umsatz:') }} <strong>{{ number_format($plan->actual_revenue_annual, 0, ',', '.') }} €</strong> ({{ $plan->coverage_ratio_percent }}%)
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <form method="POST" action="{{ route('revenueplanner.plans.sync-invoicemaker', $plan->id) }}">
                        @csrf
                        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-emerald-700">
                            ⚡ {{ __('Mit InvoiceMaker abgleichen') }}
                        </button>
                    </form>
                    <a href="{{ route('revenueplanner.wizard', $plan->id) }}" class="rounded-xl bg-[#ff9200] px-5 py-2 text-xs font-bold text-white shadow hover:bg-orange-600">
                        ✏️ {{ __('Plan bearbeiten') }}
                    </a>
                </div>
            </div>

            <!-- 4 Visual Waterfall Blocks -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-white/5 p-4 border border-white/10">
                    <p class="text-xs text-slate-400 font-semibold uppercase">{{ __('1. Kostenbasis + Ziel') }}</p>
                    <p class="mt-1 text-xl font-bold text-white">{{ number_format($plan->fixed_costs_annual + $plan->owner_compensation_annual + $plan->profit_target_annual, 0, ',', '.') }} €</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">DB-Marge: {{ $plan->contribution_margin_percent }}%</p>
                </div>
                <div class="rounded-2xl bg-white/5 p-4 border border-white/10">
                    <p class="text-xs text-slate-400 font-semibold uppercase">{{ __('2. Gesamte Umsatzlücke') }}</p>
                    <p class="mt-1 text-xl font-bold {{ $plan->revenue_gap_annual > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                        {{ number_format($plan->revenue_gap_annual, 0, ',', '.') }} €
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ number_format($plan->revenue_gap_monthly, 0, ',', '.') }} € / Mon.</p>
                </div>
                <div class="rounded-2xl bg-white/5 p-4 border border-white/10">
                    <p class="text-xs text-slate-400 font-semibold uppercase">{{ __('3. Bestandskunden-Hebel') }}</p>
                    <p class="mt-1 text-xl font-bold text-amber-400">+{{ number_format($plan->existing_customer_potential_annual, 0, ',', '.') }} €</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Restlücke: {{ number_format($plan->remaining_gap_annual, 0, ',', '.') }} €</p>
                </div>
                <div class="rounded-2xl bg-white/5 p-4 border border-white/10">
                    <p class="text-xs text-slate-400 font-semibold uppercase">{{ __('4. Monatlicher Lead-Bedarf') }}</p>
                    <p class="mt-1 text-xl font-bold text-cyan-400">{{ $plan->required_leads_monthly }} Leads</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $plan->required_won_deals_monthly }} Abschlüsse / Mon.</p>
                </div>
            </div>
        </div>

        <!-- 12-Month Seasonality Distribution Table -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('12-Monats-Zielumsatz-Verteilung') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Saisonales Verlaufsmodell: :pattern', ['pattern' => ucfirst($plan->seasonality_pattern)]) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto pt-2">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase">
                            <th class="pb-3">{{ __('Monat') }}</th>
                            <th class="pb-3 text-right">{{ __('Gewichtung (%)') }}</th>
                            <th class="pb-3 text-right">{{ __('Monats-Zielumsatz (€)') }}</th>
                            <th class="pb-3 text-right">{{ __('Geschätzter Ist-Umsatz (€)') }}</th>
                            <th class="pb-3 text-right">{{ __('Monatliche Lücke (€)') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach ($plan->monthly_distribution ?? [] as $m)
                            @php $mGap = max(0, ($m['target'] ?? 0) - ($m['actual_estimate'] ?? 0)); @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 font-bold text-slate-900">{{ $m['month'] ?? '' }}</td>
                                <td class="py-2.5 text-right text-slate-500">{{ $m['weight_percent'] ?? 8.3 }}%</td>
                                <td class="py-2.5 text-right font-bold text-cyan-800">{{ number_format($m['target'] ?? 0, 0, ',', '.') }} €</td>
                                <td class="py-2.5 text-right text-slate-700">{{ number_format($m['actual_estimate'] ?? 0, 0, ',', '.') }} €</td>
                                <td class="py-2.5 text-right font-bold {{ $mGap > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ number_format($mGap, 0, ',', '.') }} €
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- What-If Scenario Simulator & Scenarios List -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- New Simulation Box -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900">{{ __('What-If Szenario simulieren') }}</h3>
                <p class="text-xs text-slate-500">{{ __('Testen Sie den Hebel von Preiserhöhungen, Abschlussquoten und Deal-Größen.') }}</p>

                <form method="POST" action="{{ route('revenueplanner.plans.scenarios.store', $plan->id) }}" class="space-y-3 pt-2">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Szenario-Name *') }}</label>
                        <input type="text" name="title" required placeholder="z. B. +10% Preiserhöhung & +5% bessere Abschlussquote" class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="text-[10px] font-semibold text-slate-600">{{ __('Preiserhöhung (%)') }}</label>
                            <input type="number" step="1" name="price_increase_percent" value="10" class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="text-[10px] font-semibold text-slate-600">{{ __('Deal-Multiplikator') }}</label>
                            <input type="number" step="0.1" name="deal_size_multiplier" value="1.1" class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="text-[10px] font-semibold text-slate-600">{{ __('Abschlussquote Δ (%)') }}</label>
                            <input type="number" step="1" name="conversion_rate_delta_percent" value="5" class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                        </div>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-slate-900 py-2.5 text-xs font-bold text-white hover:bg-slate-800">
                        ⚡ {{ __('Szenario berechnen & speichern') }}
                    </button>
                </form>
            </div>

            <!-- Saved Scenarios -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900">{{ __('Simulierte Szenarien') }}</h3>
                <div class="space-y-3 pt-2">
                    @forelse ($plan->scenarios as $sc)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-900">{{ $sc->title }}</h4>
                                <span class="text-xs font-bold text-emerald-700">+{{ number_format($sc->simulated_annual_revenue - $plan->actual_revenue_annual, 0, ',', '.') }} €</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-200/60">
                                <span>{{ __('Simulierter Umsatz:') }} <strong>{{ number_format($sc->simulated_annual_revenue, 0, ',', '.') }} €</strong></span>
                                <span>{{ __('Benötigte Leads:') }} <strong>{{ $sc->simulated_required_leads }}</strong></span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">{{ __('Noch keine Szenarien simuliert.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
