@extends('layouts.shell')

@section('title', __('SmartKpi'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('SmartKpi') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Hierarchical KPI management with problems, actions, forecasts and goals.') }}</p>
            </div>
            <a href="{{ route('smartkpi.kpi-definitions.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New KPI') }}</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Companies') }}</div><div class="text-2xl font-bold">{{ $companies }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('KPIs') }}</div><div class="text-2xl font-bold">{{ $kpis }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Open Problems') }}</div><div class="text-2xl font-bold">{{ $problems }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Open Actions') }}</div><div class="text-2xl font-bold">{{ $openActions }}</div></div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent KPI Values') }}</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($recentValues as $kpi)
                        <li class="flex justify-between"><span>{{ $kpi->localizedName() }}</span><span class="text-slate-500">{{ $kpi->latestValue?->value ?? '-' }} {{ $kpi->unit }}</span></li>
                    @empty
                        <li class="text-slate-500">{{ __('No values yet.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Open Problems by Severity') }}</h2>
                <div class="mt-4 flex flex-wrap gap-3">
                    @foreach ($problemsBySeverity as $severity => $count)
                        <div class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium">{{ __($severity) }}: {{ $count }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
