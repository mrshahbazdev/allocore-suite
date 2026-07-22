@extends('layouts.shell')

@section('title', $kpi->exists ? __('Edit KPI') : __('New KPI'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $kpi->exists ? __('Edit KPI') : __('New KPI') }}</h1>
        <form method="POST" action="{{ $kpi->exists ? route('smartkpi.kpi-definitions.update', $kpi) : route('smartkpi.kpi-definitions.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($kpi->exists) @method('PUT') @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Name (EN)') }}</label><input type="text" name="name_en" value="{{ old('name_en', $kpi->name_en) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Name (DE)') }}</label><input type="text" name="name_de" value="{{ old('name_de', $kpi->name_de) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Description (EN)') }}</label><textarea name="description_en" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description_en', $kpi->description_en) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Description (DE)') }}</label><textarea name="description_de" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description_de', $kpi->description_de) }}</textarea></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Company') }}</label>
                    <select name="company_id" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $kpi->company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Responsible User') }}</label>
                    <select name="responsible_user_id" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ old('responsible_user_id', $kpi->responsible_user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label><input type="text" name="category" value="{{ old('category', $kpi->category) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-4">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Target') }}</label><input type="number" step="any" name="target_value" value="{{ old('target_value', $kpi->target_value) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Warning Threshold') }}</label><input type="number" step="any" name="warning_threshold" value="{{ old('warning_threshold', $kpi->warning_threshold) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Critical Threshold') }}</label><input type="number" step="any" name="critical_threshold" value="{{ old('critical_threshold', $kpi->critical_threshold) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Direction') }}</label>
                    <select name="direction" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="asc" {{ old('direction', $kpi->direction) === 'asc' ? 'selected' : '' }}>{{ __('Higher is better') }}</option>
                        <option value="desc" {{ old('direction', $kpi->direction) === 'desc' ? 'selected' : '' }}>{{ __('Lower is better') }}</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Unit') }}</label><input type="text" name="unit" value="{{ old('unit', $kpi->unit) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Formula') }}</label><input type="text" name="formula" value="{{ old('formula', $kpi->formula) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Frequency') }}</label><input type="text" name="frequency" value="{{ old('frequency', $kpi->frequency) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $kpi->is_active) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Active') }}</span></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="is_template" value="1" {{ old('is_template', $kpi->is_template) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Template') }}</span></label>
            </div>

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
