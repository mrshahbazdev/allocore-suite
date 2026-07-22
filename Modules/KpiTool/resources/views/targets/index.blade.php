@extends('layouts.shell')

@section('title', __('Targets'))
@section('page-title', __('Targets'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Monthly Targets') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Generate Targets') }}</h2>
            <form method="POST" action="{{ route('kpitool.targets.generate') }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('KPI') }}</label>
                    <select name="kpi_definition_id" class="mt-1 rounded-lg border-slate-300">
                        @foreach ($definitions as $def)
                            <option value="{{ $def->id }}">{{ $def->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Start Year') }}</label><input type="number" name="start_year" value="{{ now()->year }}" class="mt-1 rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Start Month') }}</label><input type="number" name="start_month" min="1" max="12" value="{{ now()->month }}" class="mt-1 rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Months') }}</label><input type="number" name="months" min="1" max="60" value="12" class="mt-1 rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Start Value') }}</label><input type="number" step="any" name="start_value" class="mt-1 rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Growth %') }}</label><input type="number" step="any" name="growth_rate" class="mt-1 rounded-lg border-slate-300"></div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Generate') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('KPIs') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($definitions as $def)
                    <li><a href="{{ route('kpitool.targets.show', $def) }}" class="text-indigo-600">{{ $def->name }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
