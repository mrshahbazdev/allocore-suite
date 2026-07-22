@extends('layouts.shell')

@section('title', __('KPIs'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('KPI Definitions') }}</h1>
            <a href="{{ route('smartkpi.kpi-definitions.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New KPI') }}</a>
        </div>

        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-wrap items-end gap-3">
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Search') }}</label><input type="text" name="search" value="{{ request('search') }}" class="mt-1 rounded-lg border-slate-300"></div>
            <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Company') }}</th><th class="pb-2 pr-4">{{ __('Latest') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($definitions as $kpi)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $kpi->localizedName() }}</td>
                            <td class="py-2 pr-4">{{ $kpi->company?->name }}</td>
                            <td class="py-2 pr-4">{{ $kpi->latestValue?->value ?? '-' }} {{ $kpi->unit }}</td>
                            <td class="py-2 flex gap-2"><a href="{{ route('smartkpi.kpi-definitions.show', $kpi) }}" class="text-indigo-600">{{ __('View') }}</a><a href="{{ route('smartkpi.kpi-definitions.edit', $kpi) }}" class="text-slate-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $definitions->links() }}</div>
        </div>
    </div>
@endsection
