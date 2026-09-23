@php $title = __('Umsatzplaner — Executive Dashboard'); @endphp
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

        @if (! $activePlan)
            <!-- Premium Empty State / Welcome Hero -->
            <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-orange-50/30 p-10 text-center shadow-sm md:p-16">
                <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-orange-400/10 blur-3xl"></div>
                <div class="absolute -left-16 -bottom-16 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <div class="relative mx-auto max-w-2xl space-y-6">
                    <div class="mx-auto inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-[#ff9200] to-orange-600 text-4xl shadow-xl shadow-orange-500/20 text-white">
                        📊
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-800 uppercase tracking-wider">
                            Finanzielle Klarheit & Vertriebs-Fokus
                        </span>
                        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                            {{ __('Strategischer Umsatzplaner für KMU') }}
                        </h1>
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed sm:text-base">
                            {{ __('Ermitteln Sie in 5 geführten Schritten Ihren mathematisch benötigten Mindestumsatz, decken Sie Ihre Umsatzlücke auf und leiten Sie konkrete Hebel aus Bestandskunden & Neukunden-Trichtern ab.') }}
                        </p>
                    </div>

                    <!-- 3 Feature Pillars -->
                    <div class="grid grid-cols-1 gap-4 text-left sm:grid-cols-3 pt-4">
                        <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-sm backdrop-blur">
                            <span class="text-xl">🎯</span>
                            <h4 class="mt-2 text-xs font-bold text-slate-900">{{ __('1. Zielumsatz berechnen') }}</h4>
                            <p class="mt-1 text-[11px] text-slate-500">{{ __('Fixkosten + Inhaberlohn + Gewinnziel / DB-Marge') }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-sm backdrop-blur">
                            <span class="text-xl">💎</span>
                            <h4 class="mt-2 text-xs font-bold text-slate-900">{{ __('2. Bestandskunden-Hebel') }}</h4>
                            <p class="mt-1 text-[11px] text-slate-500">{{ __('Preise, Wiederkauf & Upselling ohne Werbebudget') }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-sm backdrop-blur">
                            <span class="text-xl">⚡</span>
                            <h4 class="mt-2 text-xs font-bold text-slate-900">{{ __('3. Reverse Sales Funnel') }}</h4>
                            <p class="mt-1 text-[11px] text-slate-500">{{ __('Exakter monatlicher Lead- & Abschluss-Bedarf') }}</p>
                        </div>
                    </div>

                    <div class="pt-4 flex flex-wrap justify-center gap-4">
                        <a href="{{ route('revenueplanner.wizard') }}" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-8 py-4 text-sm font-bold text-white shadow-lg shadow-orange-500/25 transition-all hover:scale-[1.02] hover:shadow-xl">
                            ⚡ {{ __('Jetzt ersten Umsatzplan erstellen') }}
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Active Plan Executive Hero Cockpit -->
            <div class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-900 p-6 sm:p-8 text-white shadow-xl">
                <!-- Background ambient glows -->
                <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
                <div class="absolute -left-20 -bottom-20 h-72 w-72 rounded-full bg-orange-500/10 blur-3xl"></div>

                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between border-b border-white/10 pb-6">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-cyan-500/20 border border-cyan-400/30 px-3.5 py-1 text-xs font-bold text-cyan-300">
                            <span class="inline-block h-2 w-2 rounded-full bg-cyan-400 animate-pulse"></span>
                            {{ __('Aktiver Strategieplan :year', ['year' => $activePlan->fiscal_year]) }} — {{ $activePlan->name }}
                        </div>
                        <div class="mt-3 flex flex-wrap items-baseline gap-3">
                            <h1 class="text-3xl font-black sm:text-4xl tracking-tight text-white">
                                {{ number_format($activePlan->target_revenue_annual, 0, ',', '.') }} €
                            </h1>
                            <span class="text-sm font-semibold text-slate-400">/ {{ __('Jahr Zielumsatz') }}</span>
                            <span class="rounded-xl bg-white/10 px-2.5 py-1 text-xs font-semibold text-slate-300">
                                {{ number_format($activePlan->target_revenue_monthly, 0, ',', '.') }} € / {{ __('Monat') }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400 flex flex-wrap items-center gap-x-4 gap-y-1">
                            <span>💼 {{ __('Branche:') }} <strong class="text-slate-200">{{ ucfirst($activePlan->industry_type) }}</strong></span>
                            <span>📊 {{ __('Deckungsbeitrag:') }} <strong class="text-cyan-300">{{ $activePlan->contribution_margin_percent }}%</strong></span>
                            <span>🎯 {{ __('Zielerreichung:') }} <strong class="{{ $activePlan->coverage_ratio_percent >= 100 ? 'text-emerald-400' : 'text-amber-400' }}">{{ $activePlan->coverage_ratio_percent }}%</strong></span>
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <form method="POST" action="{{ route('revenueplanner.plans.sync-invoicemaker', $activePlan->id) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600/90 border border-emerald-400/30 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-600 transition">
                                🔄 {{ __('InvoiceMaker Sync') }}
                            </button>
                        </form>
                        <a href="{{ route('revenueplanner.plans.show', $activePlan->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-cyan-500/20 hover:scale-[1.02] transition">
                            🔍 {{ __('Vollständige Roadmap') }}
                        </a>
                        <a href="{{ route('revenueplanner.wizard', $activePlan->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-xs font-bold text-white hover:bg-white/20 transition">
                            ✏️ {{ __('Plan anpassen') }}
                        </a>
                    </div>
                </div>

                <!-- 4 Interactive Key Metrics -->
                <div class="relative mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4 pt-2">
                    <div class="rounded-2xl bg-white/5 p-4 border border-white/5 backdrop-blur">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Ist-Umsatz') }}</span>
                        <p class="mt-1 text-2xl font-black text-emerald-400">{{ number_format($activePlan->actual_revenue_annual, 0, ',', '.') }} €</p>
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-white/10">
                            <div class="h-full rounded-full bg-emerald-400 transition-all" style="width: {{ min(100, $activePlan->coverage_ratio_percent) }}%"></div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white/5 p-4 border border-white/5 backdrop-blur">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Umsatzlücke') }}</span>
                        <p class="mt-1 text-2xl font-black {{ $activePlan->revenue_gap_annual > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                            {{ number_format($activePlan->revenue_gap_annual, 0, ',', '.') }} €
                        </p>
                        <p class="mt-1 text-[11px] text-slate-400">{{ number_format($activePlan->revenue_gap_monthly, 0, ',', '.') }} € / {{ __('Monat') }}</p>
                    </div>

                    <div class="rounded-2xl bg-white/5 p-4 border border-white/5 backdrop-blur">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Bestandskunden-Hebel') }}</span>
                        <p class="mt-1 text-2xl font-black text-amber-400">+{{ number_format($activePlan->existing_customer_potential_annual, 0, ',', '.') }} €</p>
                        <p class="mt-1 text-[11px] text-slate-400">{{ __('Restlücke:') }} <span class="font-bold text-white">{{ number_format($activePlan->remaining_gap_annual, 0, ',', '.') }} €</span></p>
                    </div>

                    <div class="rounded-2xl bg-white/5 p-4 border border-white/5 backdrop-blur">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Neukunden-Bedarf') }}</span>
                        <p class="mt-1 text-2xl font-black text-cyan-400">{{ $activePlan->required_leads_monthly }} <span class="text-xs font-normal text-slate-300">Leads/Mon.</span></p>
                        <p class="mt-1 text-[11px] text-slate-400">{{ $activePlan->required_won_deals_monthly }} {{ __('Abschlüsse / Mon.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Strategic Charts & Roadmap -->
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Visual Waterfall Chart (2 Cols) -->
                <div class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ __('Umsatz-Wasserfall & Lückenschluss-Logik') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('Visualisierung vom Ist-Stand über Bestandskunden bis zum Neukundenziel.') }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">
                            📊 {{ __('Mathematisch validiert') }}
                        </span>
                    </div>

                    <!-- Waterfall Chart Canvas -->
                    <div class="relative h-72 w-full">
                        <canvas id="waterfallChart"></canvas>
                    </div>

                    <!-- 4-Step Explanation Grid -->
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 pt-2 border-t border-slate-100 text-xs">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <span class="font-bold text-slate-900 block">1. Ist-Umsatz</span>
                            <span class="text-emerald-700 font-bold">{{ number_format($activePlan->actual_revenue_annual, 0, ',', '.') }} €</span>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-3">
                            <span class="font-bold text-amber-950 block">2. Bestandskunden</span>
                            <span class="text-amber-800 font-bold">+{{ number_format($activePlan->existing_customer_potential_annual, 0, ',', '.') }} €</span>
                        </div>
                        <div class="rounded-xl bg-cyan-50 p-3">
                            <span class="font-bold text-cyan-950 block">3. Neukunden-Fokus</span>
                            <span class="text-cyan-800 font-bold">+{{ number_format($activePlan->remaining_gap_annual, 0, ',', '.') }} €</span>
                        </div>
                        <div class="rounded-xl bg-indigo-50 p-3">
                            <span class="font-bold text-indigo-950 block">4. Zielumsatz</span>
                            <span class="text-indigo-800 font-bold">{{ number_format($activePlan->target_revenue_annual, 0, ',', '.') }} €</span>
                        </div>
                    </div>
                </div>

                <!-- 90-Day Action Radar (1 Col) -->
                <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-base font-bold text-slate-900">{{ __('90-Tage Aktions-Radar') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Priorisierte Handlungsschritte zur Zielerreichung.') }}</p>
                    </div>

                    <div class="space-y-3">
                        @foreach ($activePlan->action_roadmap ?? [] as $action)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 transition-all hover:border-slate-300 hover:bg-white hover:shadow-sm">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="rounded-lg bg-orange-100 px-2 py-0.5 text-[10px] font-black uppercase text-orange-800">
                                        {{ $action['timeframe'] ?? 'Sofort' }}
                                    </span>
                                    <span class="rounded-full bg-slate-200/80 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                        {{ $action['lever'] ?? 'Strategie' }}
                                    </span>
                                </div>
                                <h4 class="mt-2 text-xs font-bold text-slate-900">{{ $action['title'] ?? '' }}</h4>
                                <p class="mt-1 text-[11px] text-slate-500 leading-snug">{{ $action['impact'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('revenueplanner.plans.show', $activePlan->id) }}" class="block text-center rounded-xl bg-slate-100 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                        {{ __('Alle Maßnahmen & Details ansehen') }} →
                    </a>
                </div>
            </div>

            <!-- All Plans History Table -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ __('Gespeicherte Umsatzpläne') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Historie und Szenarien Ihres Unternehmens.') }}</p>
                    </div>
                    <a href="{{ route('revenueplanner.wizard') }}" class="rounded-xl bg-[#ff9200] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-orange-600 transition">
                        + {{ __('Neuer Plan') }}
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase">
                                <th class="pb-3">{{ __('Plan-Name') }}</th>
                                <th class="pb-3">{{ __('Jahr') }}</th>
                                <th class="pb-3 text-right">{{ __('Zielumsatz (€)') }}</th>
                                <th class="pb-3 text-right">{{ __('Ist-Umsatz (€)') }}</th>
                                <th class="pb-3 text-right">{{ __('Offene Lücke (€)') }}</th>
                                <th class="pb-3 text-center">{{ __('Leads/Mon.') }}</th>
                                <th class="pb-3 text-right">{{ __('Aktionen') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach ($plans as $p)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 font-bold text-slate-900">
                                        <a href="{{ route('revenueplanner.plans.show', $p->id) }}" class="hover:text-orange-600">
                                            {{ $p->name }}
                                        </a>
                                        @if ($p->is_active)
                                            <span class="ml-1.5 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">{{ __('Aktiv') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-slate-600">{{ $p->fiscal_year }}</td>
                                    <td class="py-3 text-right font-bold text-slate-900">{{ number_format($p->target_revenue_annual, 0, ',', '.') }} €</td>
                                    <td class="py-3 text-right text-emerald-700 font-semibold">{{ number_format($p->actual_revenue_annual, 0, ',', '.') }} €</td>
                                    <td class="py-3 text-right font-bold {{ $p->revenue_gap_annual > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ number_format($p->revenue_gap_annual, 0, ',', '.') }} €
                                    </td>
                                    <td class="py-3 text-center font-bold text-cyan-800">{{ $p->required_leads_monthly }}</td>
                                    <td class="py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('revenueplanner.plans.show', $p->id) }}" class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-200">
                                                {{ __('Details') }}
                                            </a>
                                            <a href="{{ route('revenueplanner.wizard', $p->id) }}" class="rounded-lg bg-orange-50 px-2.5 py-1 text-[11px] font-bold text-orange-700 hover:bg-orange-100">
                                                {{ __('Bearbeiten') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @if ($activePlan)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ctx = document.getElementById('waterfallChart')?.getContext('2d');
                if (!ctx || typeof Chart === 'undefined') return;

                const actual = {{ (float) $activePlan->actual_revenue_annual }};
                const existing = {{ (float) $activePlan->existing_customer_potential_annual }};
                const remaining = {{ (float) $activePlan->remaining_gap_annual }};
                const target = {{ (float) $activePlan->target_revenue_annual }};

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            '{{ __('1. Ist-Umsatz') }}',
                            '{{ __('2. + Bestandskunden') }}',
                            '{{ __('3. + Neukundenbedarf') }}',
                            '{{ __('4. = Zielumsatz') }}'
                        ],
                        datasets: [{
                            label: '{{ __('Umsatzvolumen (€)') }}',
                            data: [actual, existing, remaining, target],
                            backgroundColor: [
                                '#10b981', // Emerald
                                '#f59e0b', // Amber
                                '#06b6d4', // Cyan
                                '#6366f1'  // Indigo
                            ],
                            borderRadius: 12,
                            borderSkipped: false,
                            barThickness: 48,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y.toLocaleString('de-DE') + ' €';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    callback: function(value) {
                                        return (value / 1000).toLocaleString('de-DE') + ' k€';
                                    },
                                    font: { size: 10 }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11, weight: 'bold' } }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
@endsection
