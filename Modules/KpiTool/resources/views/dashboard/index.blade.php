@extends('layouts.shell')

@section('title', __('KpiTool'))
@section('page-title', __('KpiTool Dashboard'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('KpiTool') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Bilingual KPI tracking with targets, spreadsheet, and CSV export.') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('kpitool.catalog.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Catalog') }}</a>
                <a href="{{ route('kpitool.definitions.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New KPI') }}</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Active KPIs') }}</div><div class="text-2xl font-bold">{{ $definitions }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Values') }}</div><div class="text-2xl font-bold">{{ $values }}</div></div>
            @foreach ($statusCounts as $status => $count)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __(ucfirst($status)) }}</div><div class="text-2xl font-bold">{{ $count }}</div></div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Top KPIs') }}</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($topKpis as $kpi)
                        <a href="{{ route('kpitool.definitions.show', $kpi) }}" class="flex items-center justify-between rounded-lg border border-slate-200 p-3 hover:border-indigo-300">
                            <span class="font-medium">{{ $kpi->name }}</span>
                            <span class="text-sm text-slate-500">{{ $kpi->latestValue?->value ?? '-' }} {{ $kpi->unit }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Values') }}</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($latestValues as $value)
                        <li class="flex justify-between"><span>{{ $value->kpiDefinition->name }}</span><span class="text-slate-500">{{ $value->value }} {{ $value->kpiDefinition->unit }} — {{ __($value->status) }}</span></li>
                    @empty
                        <li class="text-slate-500">{{ __('No values yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
