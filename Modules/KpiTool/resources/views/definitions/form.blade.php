@extends('layouts.shell')

@section('title', $definition->exists ? __('Edit KPI') : __('New KPI'))
@section('page-title', $definition->exists ? __('Edit KPI') : __('New KPI'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $definition->exists ? __('Edit KPI') : __('New KPI') }}</h1>
        <form method="POST" action="{{ $definition->exists ? route('kpitool.definitions.update', $definition) : route('kpitool.definitions.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($definition->exists)
                @method('PUT')
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Name (DE)') }}</label><input type="text" name="name_de" value="{{ old('name_de', $definition->name_de) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Name (EN)') }}</label><input type="text" name="name_en" value="{{ old('name_en', $definition->name_en) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Description (DE)') }}</label><textarea name="description_de" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description_de', $definition->description_de) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Description (EN)') }}</label><textarea name="description_en" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description_en', $definition->description_en) }}</textarea></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Formula') }}</label><input type="text" name="formula" value="{{ old('formula', $definition->formula) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Unit') }}</label><input type="text" name="unit" value="{{ old('unit', $definition->unit) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label><input type="text" name="category" value="{{ old('category', $definition->category) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Target') }}</label><input type="number" step="any" name="target_value" value="{{ old('target_value', $definition->target_value) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Warning Threshold') }}</label><input type="number" step="any" name="warning_threshold" value="{{ old('warning_threshold', $definition->warning_threshold) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Critical Threshold') }}</label><input type="number" step="any" name="critical_threshold" value="{{ old('critical_threshold', $definition->critical_threshold) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Frequency') }}</label>
                    <select name="frequency" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly'] as $key => $label)
                            <option value="{{ $key }}" {{ old('frequency', $definition->frequency ?? 'monthly') === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Direction') }}</label>
                    <select name="direction" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="higher_better" {{ old('direction', $definition->direction ?? 'higher_better') === 'higher_better' ? 'selected' : '' }}>{{ __('Higher is better') }}</option>
                        <option value="lower_better" {{ old('direction', $definition->direction) === 'lower_better' ? 'selected' : '' }}>{{ __('Lower is better') }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-6"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $definition->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Active') }}</span></div>
            </div>

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
