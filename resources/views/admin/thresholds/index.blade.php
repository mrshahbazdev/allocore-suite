@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('KPI Thresholds') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Manage traffic-light rules and weights for all tools.') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <div class="space-y-6">
        @foreach ($thresholds as $tool => $items)
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4 capitalize">{{ $tool }}</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-3">{{ __('KPI') }}</th>
                                <th class="px-4 py-3">{{ __('Unit') }}</th>
                                <th class="px-4 py-3">{{ __('Green') }}</th>
                                <th class="px-4 py-3">{{ __('Yellow') }}</th>
                                <th class="px-4 py-3">{{ __('Weight') }}</th>
                                <th class="px-4 py-3">{{ __('Lower is better') }}</th>
                                <th class="px-4 py-3">{{ __('Active') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($items as $threshold)
                                <tr>
                                    <form method="POST" action="{{ route('admin.thresholds.update', $threshold) }}">
                                        @csrf
                                        @method('PUT')
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-slate-900">{{ $threshold->kpi_name }}</div>
                                            <div class="text-xs text-slate-500">{{ $threshold->kpi_code }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ $threshold->unit }}</td>
                                        <td class="px-4 py-3">
                                            @if ($threshold->lower_is_better)
                                                <input type="number" step="0.01" name="green_max" value="{{ $threshold->green_max }}" class="w-24 rounded-lg border-slate-300 text-sm" placeholder="max">
                                            @else
                                                <input type="number" step="0.01" name="green_min" value="{{ $threshold->green_min }}" class="w-24 rounded-lg border-slate-300 text-sm" placeholder="min">
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($threshold->lower_is_better)
                                                <input type="number" step="0.01" name="yellow_max" value="{{ $threshold->yellow_max }}" class="w-24 rounded-lg border-slate-300 text-sm" placeholder="max">
                                            @else
                                                <input type="number" step="0.01" name="yellow_min" value="{{ $threshold->yellow_min }}" class="w-24 rounded-lg border-slate-300 text-sm" placeholder="min">
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" name="weight" value="{{ $threshold->weight }}" class="w-20 rounded-lg border-slate-300 text-sm">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="hidden" name="lower_is_better" value="0">
                                            <input type="checkbox" name="lower_is_better" value="1" @checked($threshold->lower_is_better) class="rounded border-slate-300">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" value="1" @checked($threshold->is_active) class="rounded border-slate-300">
                                        </td>
                                        <td class="px-4 py-3">
                                            <button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
                                        </td>
                                    </form>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
