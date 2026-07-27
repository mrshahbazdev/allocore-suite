@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Allocore Score') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Your company’s maturity across the corporate needs pyramid.') }}</p>
        </div>
        @if ($score)
            <a href="{{ route('audit.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Run new audit') }}</a>
        @endif
    </div>

    @if (! $score)
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
            <p class="text-lg font-semibold">{{ __('No Allocore Score yet.') }}</p>
            <p class="mt-2 text-sm">{{ __('Complete an AuditPro assessment to generate your first score.') }}</p>
            <a href="{{ route('audit.index') }}" class="mt-4 inline-block rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Start audit') }}</a>
        </div>
    @else
        <div class="mb-6 grid gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
                <p class="text-sm font-medium text-slate-500">{{ __('Current Allocore Score') }}</p>
                <div class="mt-4 flex items-end gap-3">
                    <span class="text-5xl font-extrabold text-slate-900">{{ $score->score }}</span>
                    <span class="mb-2 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ match($score->maturity_level) { 'Excellent' => 'bg-emerald-100 text-emerald-700', 'Strong' => 'bg-green-100 text-green-700', 'Solid' => 'bg-blue-100 text-blue-700', 'Weak' => 'bg-amber-100 text-amber-700', default => 'bg-red-100 text-red-700' } }}">{{ $score->maturity_level }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-500">{{ __('out of 100') }}</p>
                <p class="mt-4 text-sm text-slate-600">{{ __('Calculated') }} {{ $score->calculated_at->diffForHumans() }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h3 class="text-base font-semibold text-slate-900">{{ __('Score history') }}</h3>
                <div class="mt-4 h-56">
                    <canvas id="scoreHistoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">{{ __('Pillar breakdown') }}</h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($score->pillars as $pillar)
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">{{ $pillar['name'] }}</span>
                            <span class="text-sm font-bold text-slate-900">{{ $pillar['score'] }}</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-indigo-600" style="width: {{ min(100, max(0, $pillar['score'])) }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $pillar['maturity'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        @if (count($history) > 1)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const ctx = document.getElementById('scoreHistoryChart');
                    if (!ctx || typeof window.Chart === 'undefined') return;
                    new window.Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode(collect($history)->pluck('date')) !!},
                            datasets: [{
                                label: "{{ __('Allocore Score') }}",
                                data: {!! json_encode(collect($history)->pluck('score')) !!},
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, max: 100 },
                            },
                            plugins: { legend: { display: false } }
                        }
                    });
                });
            </script>
        @else
            <p class="mt-4 text-sm text-slate-500">{{ __('Complete more audits to see your score history.') }}</p>
        @endif
    @endif
@endsection
