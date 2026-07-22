@extends('layouts.shell')

@section('title', __('DentalTrack'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('DentalTrack Dashboard') }}</h1>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase text-slate-500">{{ __('Pending') }}</div>
                <div class="text-2xl font-bold">{{ $counts['pending'] ?? 0 }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase text-slate-500">{{ __('In Progress') }}</div>
                <div class="text-2xl font-bold">{{ $counts['in_progress'] ?? 0 }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase text-slate-500">{{ __('Completed') }}</div>
                <div class="text-2xl font-bold">{{ $counts['completed'] ?? 0 }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase text-slate-500">{{ __('Overdue') }}</div>
                <div class="text-2xl font-bold text-rose-600">{{ $counts['overdue'] ?? 0 }}</div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('In Progress Orders') }}</h2>
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($inProgress as $order)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <div class="font-medium">#{{ $order->id }} - {{ $order->patient_ref ?? '-' }}</div>
                                <div class="text-sm text-slate-500">{{ $order->productType?->name }} / {{ $order->lab?->name }}</div>
                            </div>
                            <div class="text-right text-sm">
                                <div class="uppercase text-xs text-slate-500">{{ $order->priority->value }}</div>
                                <div>{{ $order->progressPercentage() }}%</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-sm text-slate-500">{{ __('No in progress orders.') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Workstations') }}</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @forelse ($workstations as $ws)
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="font-medium">{{ $ws->name }}</div>
                            <div class="text-sm text-slate-500">{{ $ws->lab?->name }}</div>
                            <div class="mt-2 text-xs font-semibold {{ $ws->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $ws->is_active ? __('Active') : __('Inactive') }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">{{ __('No workstations.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
