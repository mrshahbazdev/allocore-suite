@php $title = $plan->name . ' — ' . __('Strategische Umsatz-Roadmap'); @endphp
@extends('layouts.shell')

@section('content')
    @include('revenueplanner::partials.nav')

    <div class="space-y-8" data-no-navigate>
        @if (session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm font-semibold text-emerald-900 shadow-sm backdrop-blur">
                <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-500 text-white text-xs">✓</span>
                {{ session('status') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 text-sm font-semibold text-amber-900 shadow-sm backdrop-blur">
                <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-amber-500 text-white text-xs">!</span>
                {{ session('warning') }}
            </div>
        @endif

        <!-- Executive Summary Banner -->
        <div class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-900 p-6 sm:p-8 text-white shadow-xl space-y-6">
            <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl"></div>
            <div class="absolute -left-16 -bottom-16 h-64 w-64 rounded-full bg-orange-500/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b border-white/10 pb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-cyan-400/20 border border-cyan-400/30 px-3 py-1 text-xs font-bold text-cyan-300 uppercase tracking-wider">
                            {{ $plan->fiscal_year }} {{ __('Umsatz-Strategie') }}
                        </span>
                        @if ($plan->is_active)
                            <span class="rounded-full bg-emerald-500/20 border border-emerald-400/30 px-3 py-1 text-xs font-bold text-emerald-300">
                                ● {{ __('Aktiv') }}
                            </span>
                        @endif
                    </div>
                    <h1 class="mt-3 text-3xl font-black sm:text-4xl">{{ $plan->name }}</h1>
                    <p class="text-xs text-slate-400 mt-1 flex flex-wrap gap-x-4">
                        <span>{{ __('Berechneter Zielumsatz:') }} <strong class="text-white">{{ number_format($plan->target_revenue_annual, 0, ',', '.') }} €</strong></span>
                        <span>{{ __('Ist-Umsatz:') }} <strong class="text-emerald-400">{{ number_format($plan->actual_revenue_annual, 0, ',', '.') }} €</strong> ({{ $plan->coverage_ratio_percent }}%)</span>
                        <span>{{ __('DB-Marge:') }} <strong class="text-cyan-300">{{ $plan->contribution_margin_percent }}%</strong></span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <form method="POST" action="{{ route('revenueplanner.plans.sync-invoicemaker', $plan->id) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-emerald-700 transition">
                            🔄 {{ __('InvoiceMaker Sync') }}
                        </button>
                    </form>
                    <a href="{{ route('revenueplanner.wizard', $plan->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-orange-500/20 hover:from-orange-600 hover:to-orange-700 transition">
                        ✏️ {{ __('Plan bearbeiten') }}
                    </a>
                </div>
            </div>

            <!-- 4 Visual Waterfall Blocks -->
            <div class="relative grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-white/5 p-4 border border-white/10 backdrop-blur">
                    <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">{{ __('1. Kostenbasis + Ziel') }}</span>
                    <p class="mt-1 text-xl font-black text-white">{{ number_format($plan->fixed_costs_annual + $plan->owner_compensation_annual + $plan->profit_target_annual, 0, ',', '.') }} €</p>
                    <p class="text-[11px] text-slate-400 mt-1">DB-Marge: {{ $plan->contribution_margin_percent }}%</p>
                </div>
                <div class="rounded-2xl bg-white/5 p-4 border border-white/10 backdrop-blur">
                    <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">{{ __('2. Gesamte Umsatzlücke') }}</span>
                    <p class="mt-1 text-xl font-black {{ $plan->revenue_gap_annual > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                        {{ number_format($plan->revenue_gap_annual, 0, ',', '.') }} €
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ number_format($plan->revenue_gap_monthly, 0, ',', '.') }} € / {{ __('Monat') }}</p>
                </div>
                <div class="rounded-2xl bg-white/5 p-4 border border-white/10 backdrop-blur">
                    <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">{{ __('3. Bestandskunden-Hebel') }}</span>
                    <p class="mt-1 text-xl font-black text-amber-400">+{{ number_format($plan->existing_customer_potential_annual, 0, ',', '.') }} €</p>
                    <p class="text-[11px] text-slate-400 mt-1">Restlücke: {{ number_format($plan->remaining_gap_annual, 0, ',', '.') }} €</p>
                </div>
                <div class="rounded-2xl bg-white/5 p-4 border border-white/10 backdrop-blur">
                    <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">{{ __('4. Lead-Bedarf / Monat') }}</span>
                    <p class="mt-1 text-xl font-black text-cyan-400">{{ $plan->required_leads_monthly }} Leads</p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ $plan->required_won_deals_monthly }} {{ __('Abschlüsse / Mon.') }}</p>
                </div>
            </div>
        </div>

        <!-- 12-Month Seasonality Distribution & Chart -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('12-Monats-Zielumsatz-Verteilung & Saisonalität') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Saisonales Verlaufsmodell: :pattern', ['pattern' => ucfirst($plan->seasonality_pattern)]) }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">
                    📈 Monatsphasierung
                </span>
            </div>

            <!-- Seasonality Chart -->
            <div class="relative h-64 w-full">
                <canvas id="seasonalityChart"></canvas>
            </div>

            <!-- Table breakdown -->
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
                            <tr class="hover:bg-slate-50 transition">
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
            <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">{{ __('What-If Szenario simulieren') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Testen Sie den Hebel von Preiserhöhungen, Abschlussquoten und Deal-Größen.') }}</p>
                </div>

                <form method="POST" action="{{ route('revenueplanner.plans.scenarios.store', $plan->id) }}" class="space-y-4 pt-1">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Szenario-Name *') }}</label>
                        <input type="text" name="title" required placeholder="z. B. +10% Preiserhöhung & +5% bessere Abschlussquote" class="mt-1 w-full rounded-xl border-slate-300 text-xs font-semibold">
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[10px] font-semibold text-slate-600">{{ __('Preiserhöhung (%)') }}</label>
                            <input type="number" step="1" name="price_increase_percent" value="10" class="mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[10px] font-semibold text-slate-600">{{ __('Deal-Multiplikator') }}</label>
                            <input type="number" step="0.1" name="deal_size_multiplier" value="1.1" class="mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[10px] font-semibold text-slate-600">{{ __('Abschlussquote Δ (%)') }}</label>
                            <input type="number" step="1" name="conversion_rate_delta_percent" value="5" class="mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 py-3 text-xs font-bold text-white shadow-md hover:from-slate-800 hover:to-slate-700 transition">
                        ⚡ {{ __('Szenario berechnen & speichern') }}
                    </button>
                </form>
            </div>

            <!-- Saved Scenarios -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">{{ __('Simulierte Szenarien') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Vergleich alternativer Vertriebs- und Preisstrategien.') }}</p>
                </div>
                <div class="space-y-3 pt-1">
                    @forelse ($plan->scenarios as $sc)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 space-y-2 transition hover:border-slate-300 hover:bg-white">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-900">{{ $sc->title }}</h4>
                                <span class="text-xs font-black text-emerald-700">+{{ number_format($sc->simulated_annual_revenue - $plan->actual_revenue_annual, 0, ',', '.') }} €</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-200/60">
                                <span>{{ __('Simulierter Umsatz:') }} <strong>{{ number_format($sc->simulated_annual_revenue, 0, ',', '.') }} €</strong></span>
                                <span>{{ __('Benötigte Leads:') }} <strong>{{ $sc->simulated_required_leads }}</strong></span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            {{ __('Noch keine Szenarien simuliert. Erstellen Sie links ein erstes Szenario!') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('seasonalityChart')?.getContext('2d');
            if (!ctx || typeof Chart === 'undefined') return;

            const months = @json(collect($plan->monthly_distribution ?? [])->pluck('month'));
            const targets = @json(collect($plan->monthly_distribution ?? [])->pluck('target'));
            const actuals = @json(collect($plan->monthly_distribution ?? [])->pluck('actual_estimate'));

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: '{{ __('Zielumsatz (€)') }}',
                            data: targets,
                            borderColor: '#06b6d4',
                            backgroundColor: 'rgba(6, 182, 212, 0.1)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointBackgroundColor: '#06b6d4'
                        },
                        {
                            label: '{{ __('Geschätzter Ist-Umsatz (€)') }}',
                            data: actuals,
                            borderColor: '#10b981',
                            borderDash: [5, 5],
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#10b981',
                            fill: false,
                            tension: 0.2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString('de-DE') + ' €';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(val) {
                                    return (val / 1000).toLocaleString('de-DE') + ' k€';
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
@endsection
