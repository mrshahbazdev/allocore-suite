@extends('layouts.shell')

@section('title', $value->exists ? __('Edit Value') : __('New Value'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $kpi->localizedName() }} — {{ $value->exists ? __('Edit Value') : __('New Value') }}</h1>
        <form method="POST" action="{{ route('smartkpi.kpi-definitions.kpi-values.store', $kpi) }}" class="mt-6 space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Value') }}</label><input type="number" step="any" name="value" value="{{ old('value', $value->value) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Recorded At') }}</label><input type="date" name="recorded_at" value="{{ old('recorded_at', $value->recorded_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            </div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label><textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('notes', $value->notes) }}</textarea></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
