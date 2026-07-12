@extends('layouts.shell')

@section('content')
    @include('auditpro::partials.nav')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('Completed assessment') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">{{ $audit->team->name }}</h1>
            <p class="text-sm text-slate-500">{{ $audit->template?->name }} · {{ $audit->updated_at->format('F d, Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('audit.report', $audit) }}" target="_blank" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Print report') }}</a>
            <a href="{{ route('audit.compare') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Compare') }}</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
            <p class="text-sm font-medium text-slate-500">{{ __('Overall maturity') }}</p>
            <div class="mt-4 flex items-end gap-2">
                <span class="text-6xl font-bold tracking-tight text-indigo-700">{{ number_format($overallScore, 1) }}</span>
                <span class="pb-2 text-lg text-slate-400">/ 5</span>
            </div>
            <span class="mt-4 inline-flex rounded-full bg-indigo-100 px-3 py-1 text-sm font-semibold text-indigo-700">{{ __($overallMaturity) }}</span>
            <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Performed by') }}</dt><dd class="font-medium text-slate-700">{{ $audit->creator?->name ?? __('Deleted user') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Industry') }}</dt><dd class="font-medium text-slate-700">{{ $audit->team->industry ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Company size') }}</dt><dd class="font-medium text-slate-700">{{ $audit->team->size ?? '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="font-semibold text-slate-900">{{ __('Pillar radar') }}</h2>
            <div class="mx-auto mt-4 max-w-lg"><canvas id="auditRadar" height="280"></canvas></div>
        </section>
    </div>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">{{ __('Pillar breakdown') }}</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach ($audit->results->sortByDesc('average_score') as $result)
                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $result->level }}</h3>
                            <p class="text-sm text-slate-500">{{ __($result->maturity_level) }}</p>
                        </div>
                        <span class="text-lg font-bold text-indigo-700">{{ number_format((float) $result->average_score, 1) }}/5</span>
                    </div>
                    <div class="mt-3 h-2.5 rounded-full bg-slate-100">
                        <div class="h-2.5 rounded-full bg-indigo-500" style="width: {{ min(100, ((float) $result->average_score / 5) * 100) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900">{{ __('Priority recommendations') }}</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            @foreach ($audit->results->sortBy('average_score')->take(3) as $result)
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="font-semibold text-amber-900">{{ $result->level }}</p>
                    <p class="mt-1 text-sm text-amber-800">{{ __('Focus improvement planning on this pillar, currently scoring :score out of 5.', ['score' => number_format((float) $result->average_score, 1)]) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('auditRadar');
            if (!canvas || !window.Chart) return;

            new window.Chart(canvas, {
                type: 'radar',
                data: {
                    labels: @json($radarLabels),
                    datasets: [{
                        label: @json(__('Maturity score')),
                        data: @json($radarScores),
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.18)',
                        pointBackgroundColor: '#4f46e5',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    scales: { r: { beginAtZero: true, min: 0, max: 5, ticks: { stepSize: 1 } } },
                    plugins: { legend: { display: false } },
                },
            });
        });
    </script>
@endsection
