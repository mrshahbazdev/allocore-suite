@extends('layouts.shell')

@section('title', $problem->exists ? __('Edit Problem') : __('New Problem'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $problem->exists ? __('Edit Problem') : __('New Problem') }}</h1>
        <form method="POST" action="{{ $problem->exists ? route('smartkpi.problems.update', $problem) : route('smartkpi.problems.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($problem->exists) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('KPI') }}</label>
                <select name="kpi_definition_id" class="mt-1 w-full rounded-lg border-slate-300">
                    @foreach ($kpis as $kpi)
                        <option value="{{ $kpi->id }}" {{ old('kpi_definition_id', $problem->kpi_definition_id) == $kpi->id ? 'selected' : '' }}>{{ $kpi->localizedName() }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $problem->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label><textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $problem->description) }}</textarea></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Severity') }}</label>
                    <select name="severity" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach (['warning', 'critical', 'anomaly'] as $s)
                            <option value="{{ $s }}" {{ old('severity', $problem->severity) === $s ? 'selected' : '' }}>{{ __($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach (['open', 'in_progress', 'resolved', 'closed'] as $s)
                            <option value="{{ $s }}" {{ old('status', $problem->status) === $s ? 'selected' : '' }}>{{ __($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
