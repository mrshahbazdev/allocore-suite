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

                @if ($benchmark !== null)
                    <div class="mt-6 rounded-lg bg-slate-50 p-4">
                        <p class="text-sm text-slate-600">{{ __('Industry benchmark') }}</p>
                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ __('Better than :percent% in :industry', ['percent' => $benchmark, 'industry' => $score->industry_sub ?: $score->industry]) }}</p>
                    </div>
                @endif

                @if ($industryStats && $industryStats['count'])
                    <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                        <div class="rounded-lg bg-slate-50 p-3">
                            <p class="text-xs text-slate-500">{{ __('Industry avg') }}</p>
                            <p class="font-semibold text-slate-900">{{ $industryStats['average'] }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <p class="text-xs text-slate-500">{{ __('Benchmarks') }}</p>
                            <p class="font-semibold text-slate-900">{{ $industryStats['count'] }}</p>
                        </div>
                    </div>
                @endif
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

        @if (count($history) > 0)
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-2">{{ __('Date') }}</th>
                            <th class="px-4 py-2">{{ __('Score') }}</th>
                            <th class="px-4 py-2">{{ __('Maturity') }}</th>
                            <th class="px-4 py-2">{{ __('Change') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php($previous = null)
                        @foreach (array_reverse($history) as $entry)
                            @php($change = $previous === null ? null : round($entry['score'] - $previous, 1))
                            @php($previous = $entry['score'])
                            <tr>
                                <td class="px-4 py-2 text-slate-700">{{ $entry['date'] }}</td>
                                <td class="px-4 py-2 font-semibold text-slate-900">{{ $entry['score'] }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $entry['maturity'] }}</td>
                                <td class="px-4 py-2">
                                    @if ($change === null)
                                        —
                                    @elseif ($change > 0)
                                        <span class="text-emerald-600">+{{ $change }}</span>
                                    @elseif ($change < 0)
                                        <span class="text-rose-600">{{ $change }}</span>
                                    @else
                                        <span class="text-slate-500">0</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    @include('partials.allocore-recommendations', ['recommendations' => $recommendations])

    @if ($team && Auth::id() === $team->owner_id)
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">{{ __('Public scorecard') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Share a read-only version of your Allocore Score.') }}</p>

            <form method="POST" action="{{ route('allocore-score.public.update') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="public_score_enabled" id="public_score_enabled" value="1" @checked($team->public_score_enabled) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="public_score_enabled" class="text-sm font-medium text-slate-700">{{ __('Enable public scorecard') }}</label>
                </div>

                <div>
                    <label for="public_score_slug" class="block text-sm font-medium text-slate-700">{{ __('Public slug') }}</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500">{{ url('scorecard/') }}/</span>
                        <input type="text" name="public_score_slug" id="public_score_slug" value="{{ old('public_score_slug', $team->public_score_slug) }}" class="block w-full rounded-none rounded-r-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="my-company">
                    </div>
                    @error('public_score_slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                @if ($team->public_score_enabled && $team->public_score_slug)
                    <p class="text-sm text-slate-600">
                        {{ __('Public URL:') }} <a href="{{ route('scorecard.public', $team->public_score_slug) }}" target="_blank" class="font-medium text-indigo-600 hover:underline">{{ route('scorecard.public', $team->public_score_slug) }}</a>
                    </p>
                    <p class="text-sm text-slate-600">
                        <a href="{{ route('scorecard.certificate', $team->public_score_slug) }}" target="_blank" class="font-medium text-indigo-600 hover:underline">{{ __('Allocore Score certificate') }}</a>
                    </p>
                    @if ($embedCode)
                        <div>
                            <label for="embed-code" class="block text-sm font-medium text-slate-700">{{ __('Embed code for your website') }}</label>
                            <textarea id="embed-code" readonly rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-xs font-mono text-slate-600">{{ $embedCode }}</textarea>
                        </div>
                    @endif
                @endif

                <div class="flex items-center justify-end">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    @endif
@endsection
