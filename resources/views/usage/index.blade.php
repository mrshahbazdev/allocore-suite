@extends('layouts.shell', ['title' => __('Usage Analytics')])

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Usage Analytics') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Track how your tools are used over the last 30 days.') }}</p>
    </div>
    <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Back to dashboard') }}</a>
</div>

<div class="mb-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="text-sm text-slate-500">{{ __('Records created (30d)') }}</div>
        <div class="text-3xl font-bold text-indigo-600">{{ number_format($analytics['totals']['records']) }}</div>
    </div>
    <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="text-sm text-slate-500">{{ __('Activities logged') }}</div>
        <div class="text-3xl font-bold text-emerald-600">{{ number_format($analytics['totals']['activities']) }}</div>
    </div>
    <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="text-sm text-slate-500">{{ __('Subscriptions events') }}</div>
        <div class="text-3xl font-bold text-amber-600">{{ number_format($analytics['totals']['subscriptions']) }}</div>
    </div>
</div>

<div class="mb-6 rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
    <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Module activity over time') }}</h2>
    <div class="relative h-72 w-full">
        <canvas id="moduleChart"></canvas>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Daily activity') }}</h2>
        <div class="relative h-64 w-full">
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Subscription events') }}</h2>
        <div class="relative h-64 w-full">
            <canvas id="subscriptionChart"></canvas>
        </div>
    </div>
</div>

@if (! empty($analytics['per_module']))
    <div class="mt-6 rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Records by module') }}</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Module') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Records (30d)') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($analytics['per_module'] as $module)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $module['name'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ number_format($module['total']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@push('scripts')
<script>
    const chartLabels = @json($analytics['dates']);

    new Chart(document.getElementById('moduleChart'), {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: @json($analytics['datasets'])
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    new Chart(document.getElementById('activityChart'), {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: '{{ __('Activities') }}',
                data: @json($analytics['activity']),
                backgroundColor: 'rgba(16, 185, 129, 0.6)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    new Chart(document.getElementById('subscriptionChart'), {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: '{{ __('Subscriptions') }}',
                data: @json($analytics['subscriptions']),
                backgroundColor: 'rgba(245, 158, 11, 0.6)',
                borderColor: 'rgba(245, 158, 11, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
</script>
@endpush
@endsection
