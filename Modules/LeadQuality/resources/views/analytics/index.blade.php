@extends('layouts.shell')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Analytics') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Growth, fulfillment, and lead quality trends.') }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-slate-500">{{ __('30-day fulfillment') }}</div>
                <div class="mt-2 text-3xl font-semibold text-indigo-600">{{ $fulfillment['success_rate'] }}%</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-slate-500">{{ __('Completed activities') }}</div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">{{ $fulfillment['total_completed'] }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-slate-500">{{ __('Top industries') }}</div>
                <div class="mt-2 text-sm text-slate-700">
                    @forelse ($industryMap as $industry => $count)
                        <div class="flex items-center justify-between py-1">
                            <span>{{ $industry ?: __('Unknown') }}</span>
                            <span class="font-medium">{{ $count }}</span>
                        </div>
                    @empty
                        <div>{{ __('No data yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Lead growth') }}</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-6">
                @foreach ($growthData as $row)
                    <div class="rounded-xl bg-slate-50 p-3 text-center">
                        <div class="text-xs text-slate-500">{{ $row['label'] }}</div>
                        <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $row['count'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
