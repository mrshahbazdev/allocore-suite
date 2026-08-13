@extends('layouts.public')

@section('title', __('Beispiel-Allocore-Audit'))
@section('meta_description', __('Sehen Sie ein konkretes Beispiel eines Allocore Audits mit Score, Radar und Empfehlungen.'))

@section('content')
    <section class="py-16 lg:py-24">
        <div class="mx-auto max-w-5xl px-6 lg:px-8">
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">{{ __('Audit Example') }}</p>
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">{{ __('So sieht Ihr Allocore Audit aus') }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">{{ __('Ein transparenter Einblick in den Bewertungsprozess, die Ergebnisdarstellung und die daraus abgeleiteten Handlungsempfehlungen.') }}</p>
            </div>

            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm lg:col-span-1">
                    <p class="text-sm font-medium text-slate-500">{{ __('Allocore Score') }}</p>
                    <div class="mt-4 flex items-end gap-2">
                        <span class="text-6xl font-extrabold text-slate-900">{{ $score->score }}</span>
                        <span class="mb-2 text-lg text-slate-400">/ 100</span>
                    </div>
                    <span class="mt-4 inline-flex rounded-full px-3 py-1 text-sm font-semibold
                        {{ match($score->maturity_level) { 'Excellent' => 'bg-emerald-100 text-emerald-700', 'Strong' => 'bg-green-100 text-green-700', 'Solid' => 'bg-blue-100 text-blue-700', 'Weak' => 'bg-amber-100 text-amber-700', default => 'bg-red-100 text-red-700' } }}">
                        {{ __($score->maturity_level) }}
                    </span>
                    <p class="mt-6 text-sm text-slate-500">{{ __('Dieses Beispiel zeigt die Auswertung eines typischen mittelständischen Dienstleisters.') }}</p>
                    <a href="{{ route('audit-example.pdf') }}" class="mt-6 inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Beispiel-PDF herunterladen') }}</a>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm lg:col-span-2">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Pillar-Radar') }}</h2>
                    <div class="mx-auto mt-6 max-w-md"><canvas id="auditExampleRadar" height="280"></canvas></div>
                </div>
            </div>

            <div class="mt-12 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Pillar-Ergebnisse') }}</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($score->pillars as $pillar)
                        <div class="rounded-xl border border-slate-100 p-5">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-900">{{ __($pillar['name']) }}</span>
                                <span class="text-lg font-bold text-slate-900">{{ $pillar['score'] }}</span>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-indigo-600" style="width: {{ min(100, max(0, $pillar['score'])) }}%"></div>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">{{ __($pillar['maturity']) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-12 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Priorisierte Empfehlungen') }}</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    @foreach (collect($score->pillars)->sortBy('score')->take(3) as $pillar)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                            <p class="font-semibold text-amber-900">{{ __($pillar['name']) }}</p>
                            <p class="mt-2 text-sm text-amber-800">
                                @if ($pillar['name'] === 'Revenue')
                                    {{ __('Umsatztransparenz und Rechnungsstellung verbessern.') }}
                                @elseif ($pillar['name'] === 'Profit')
                                    {{ __('Rentabilitätsanalyse und Cashflow-Steuerung stärken.') }}
                                @elseif ($pillar['name'] === 'Order')
                                    {{ __('Projekte, Zeiterfassung und Abläufe optimieren.') }}
                                @elseif ($pillar['name'] === 'Influence')
                                    {{ __('SEO, Leads und Reichweite systematisch ausbauen.') }}
                                @elseif ($pillar['name'] === 'Legacy')
                                    {{ __('Vision, Kultur und langfristige Strategie ausrichten.') }}
                                @else
                                    {{ __('Diesen Bereich als nächstes priorisieren.') }}
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('audit.index') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-6 py-3 text-base font-semibold text-white hover:bg-slate-700">{{ __('Eigenen Audit starten') }}</a>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('auditExampleRadar');
            if (!canvas || !window.Chart) return;

            new window.Chart(canvas, {
                type: 'radar',
                data: {
                    labels: {!! json_encode(collect($score->pillars)->pluck('name')->map(fn ($n) => __($n))) !!},
                    datasets: [{
                        label: "{{ __('Maturity score') }}",
                        data: {!! json_encode(collect($score->pillars)->pluck('score')->map(fn ($s) => ($s / 100) * 4)) !!},
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.18)',
                        pointBackgroundColor: '#4f46e5',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    scales: { r: { beginAtZero: true, min: 0, max: 4, ticks: { stepSize: 1 } } },
                    plugins: { legend: { display: false } },
                },
            });
        });
    </script>
@endsection
