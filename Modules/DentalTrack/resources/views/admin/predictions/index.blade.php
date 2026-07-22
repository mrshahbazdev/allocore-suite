@extends('layouts.shell')

@section('title', __('Predictions'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Predictions Dashboard') }}</h1>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Avg Accuracy') }}</div><div class="text-2xl font-bold">{{ $stats['avg_accuracy'] }}%</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Total Predictions') }}</div><div class="text-2xl font-bold">{{ $stats['total_predictions'] }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Recent Accuracy') }}</div><div class="text-2xl font-bold">{{ $stats['recent_accuracy'] }}%</div></div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Smart Suggestions') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($suggestions as $s)
                    <div class="rounded-lg border {{ $s['priority'] === 'high' ? 'border-rose-200 bg-rose-50' : 'border-indigo-200 bg-indigo-50' }} p-3 text-sm">
                        <span class="font-semibold">{{ $s['type'] }}:</span> {{ $s['message'] }}
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('No suggestions at this time.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
