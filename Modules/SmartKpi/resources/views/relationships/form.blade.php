@extends('layouts.shell')

@section('title', $relationship->exists ? __('Edit Relationship') : __('New Relationship'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $relationship->exists ? __('Edit Relationship') : __('New Relationship') }}</h1>
        <form method="POST" action="{{ $relationship->exists ? route('smartkpi.relationships.update', $relationship) : route('smartkpi.relationships.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($relationship->exists) @method('PUT') @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Cause KPI') }}</label>
                    <select name="cause_kpi_id" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach ($kpis as $kpi)
                            <option value="{{ $kpi->id }}" {{ old('cause_kpi_id', $relationship->cause_kpi_id) == $kpi->id ? 'selected' : '' }}>{{ $kpi->localizedName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Effect KPI') }}</label>
                    <select name="effect_kpi_id" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach ($kpis as $kpi)
                            <option value="{{ $kpi->id }}" {{ old('effect_kpi_id', $relationship->effect_kpi_id) == $kpi->id ? 'selected' : '' }}>{{ $kpi->localizedName() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Lag Periods') }}</label><input type="number" name="lag_periods" value="{{ old('lag_periods', $relationship->lag_periods) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Correlation (-1 to 1)') }}</label><input type="number" step="0.01" min="-1" max="1" name="correlation" value="{{ old('correlation', $relationship->correlation) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $relationship->is_active) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Active') }}</span></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
