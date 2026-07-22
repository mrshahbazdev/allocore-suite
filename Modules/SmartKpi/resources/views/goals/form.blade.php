@extends('layouts.shell')

@section('title', $goal->exists ? __('Edit Goal') : __('New Goal'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $goal->exists ? __('Edit Goal') : __('New Goal') }}</h1>
        <form method="POST" action="{{ $goal->exists ? route('smartkpi.goals.update', $goal) : route('smartkpi.goals.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($goal->exists) @method('PUT') @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Name (EN)') }}</label><input type="text" name="name_en" value="{{ old('name_en', $goal->name_en) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Name (DE)') }}</label><input type="text" name="name_de" value="{{ old('name_de', $goal->name_de) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Company') }}</label>
                    <select name="company_id" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $goal->company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('KPI') }}</label>
                    <select name="kpi_definition_id" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($kpis as $kpi)
                            <option value="{{ $kpi->id }}" {{ old('kpi_definition_id', $goal->kpi_definition_id) == $kpi->id ? 'selected' : '' }}>{{ $kpi->localizedName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach (['active', 'achieved', 'missed'] as $s)
                            <option value="{{ $s }}" {{ old('status', $goal->status) === $s ? 'selected' : '' }}>{{ __($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Target Value') }}</label><input type="number" step="any" name="target_value" value="{{ old('target_value', $goal->target_value) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Current Value') }}</label><input type="number" step="any" name="current_value" value="{{ old('current_value', $goal->current_value) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Deadline') }}</label><input type="date" name="deadline" value="{{ old('deadline', $goal->deadline?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
