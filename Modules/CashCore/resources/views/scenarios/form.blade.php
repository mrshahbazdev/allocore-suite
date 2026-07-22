@extends('layouts.shell')

@section('title', __('New Scenario'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('New Scenario') }}</h1>
        <form method="POST" action="{{ route('cashcore.scenarios.store') }}" class="mt-6 space-y-4">
            @csrf
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label><input type="text" name="name" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Current Revenue') }}</label><input type="number" step="0.01" name="current_revenue" value="{{ $currentRevenue }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Current Costs') }}</label><input type="number" step="0.01" name="current_costs" value="{{ $currentCosts }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Adjusted Revenue') }}</label><input type="number" step="0.01" name="adjusted_revenue" value="0" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Adjusted Costs') }}</label><input type="number" step="0.01" name="adjusted_costs" value="0" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save Scenario') }}</button>
        </form>
    </div>
@endsection
