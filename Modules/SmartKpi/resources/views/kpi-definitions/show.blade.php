@extends('layouts.shell')

@section('title', $kpiDefinition->localizedName())

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $kpiDefinition->localizedName() }}</h1>
                <p class="text-sm text-slate-500">{{ $kpiDefinition->localizedDescription() }}</p>
                <div class="mt-2 text-xs text-slate-500">{{ $kpiDefinition->company?->name }} — {{ $kpiDefinition->category }} — {{ $kpiDefinition->unit }}</div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('smartkpi.kpi-definitions.kpi-values.create', $kpiDefinition) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Value') }}</a>
                <form method="POST" action="{{ route('smartkpi.kpi-definitions.forecasts.store', $kpiDefinition) }}" class="inline">@csrf<button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Forecast') }}</button></form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('History') }}</h2>
                <table class="mt-3 min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Date') }}</th><th class="pb-2 pr-4">{{ __('Value') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2"></th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($kpiDefinition->values as $value)
                            <tr>
                                <td class="py-2 pr-4">{{ $value->recorded_at->format('Y-m-d') }}</td>
                                <td class="py-2 pr-4">{{ $value->value }} {{ $kpiDefinition->unit }}</td>
                                <td class="py-2 pr-4"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $value->status === 'critical' ? 'bg-rose-100 text-rose-700' : ($value->status === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">{{ __($value->status) }}</span></td>
                                <td class="py-2">
                                    <form method="POST" action="{{ route('smartkpi.kpi-values.destroy', $value) }}" class="inline">@csrf @method('DELETE')<button class="text-rose-600">{{ __('Delete') }}</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Problems & Actions') }}</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($kpiDefinition->problems as $problem)
                        <li><a href="{{ route('smartkpi.problems.show', $problem) }}" class="text-indigo-600">{{ $problem->title }}</a> <span class="text-slate-500">({{ $problem->status }})</span></li>
                    @empty
                        <li class="text-slate-500">{{ __('No problems.') }}</li>
                    @endforelse
                </ul>

                <h2 class="mt-6 text-lg font-semibold text-slate-900">{{ __('Forecasts') }}</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($kpiDefinition->forecasts as $forecast)
                        <li><a href="{{ route('smartkpi.forecasts.show', $forecast) }}" class="text-indigo-600">{{ $forecast->method }} {{ $forecast->horizon }}</a>: {{ $forecast->value }}</li>
                    @empty
                        <li class="text-slate-500">{{ __('No forecasts.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
