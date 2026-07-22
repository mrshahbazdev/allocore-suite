@extends('layouts.shell')

@section('title', __('Targets for :name', ['name' => $kpiDefinition->name]))
@section('page-title', __('Targets for :name', ['name' => $kpiDefinition->name]))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ $kpiDefinition->name }}</h1>
            <a href="{{ route('kpitool.targets.index') }}" class="text-indigo-600">{{ __('Back') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Add / Update Target') }}</h2>
            <form method="POST" action="{{ route('kpitool.targets.store', $kpiDefinition) }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Year') }}</label><input type="number" name="year" value="{{ now()->year }}" class="mt-1 rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Month') }}</label><input type="number" name="month" min="1" max="12" value="{{ now()->month }}" class="mt-1 rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Target') }}</label><input type="number" step="any" name="target_value" class="mt-1 rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Growth %') }}</label><input type="number" step="any" name="growth_rate" class="mt-1 rounded-lg border-slate-300"></div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Month') }}</th><th class="pb-2 pr-4">{{ __('Target') }}</th><th class="pb-2 pr-4">{{ __('Growth %') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($targets as $target)
                        <tr>
                            <td class="py-2 pr-4">{{ $target->year }}-{{ str_pad($target->month, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-2 pr-4">{{ $target->target_value }}</td>
                            <td class="py-2 pr-4">{{ $target->growth_rate ?? '-' }}</td>
                            <td class="py-2">
                                <form method="POST" action="{{ route('kpitool.targets.destroy', $target) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
