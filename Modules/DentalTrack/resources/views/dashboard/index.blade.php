@extends('layouts.shell')

@section('title', __('DentalTrack'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('DentalTrack Dashboard') }}</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dentaltrack.track') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Track Order') }}</a>
                <a href="{{ route('dentaltrack.scan.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Scan QR') }}</a>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('dentaltrack.track') }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50">
                <div class="text-xs uppercase text-slate-500">{{ __('Track Order') }}</div>
                <div class="mt-1 text-sm font-medium text-indigo-700">{{ __('Enter tracking code') }}</div>
            </a>
            <a href="{{ route('dentaltrack.scan.index') }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50">
                <div class="text-xs uppercase text-slate-500">{{ __('Scan QR') }}</div>
                <div class="mt-1 text-sm font-medium text-indigo-700">{{ __('Workstation → Order') }}</div>
            </a>
            @if (auth()->user()?->isAdmin())
                <a href="{{ route('dentaltrack.admin.orders.index') }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50">
                    <div class="text-xs uppercase text-slate-500">{{ __('Manage Orders') }}</div>
                    <div class="mt-1 text-sm font-medium text-indigo-700">{{ __('Add & update orders') }}</div>
                </a>
                <a href="{{ route('dentaltrack.admin.workstations.index') }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50">
                    <div class="text-xs uppercase text-slate-500">{{ __('Workstations') }}</div>
                    <div class="mt-1 text-sm font-medium text-indigo-700">{{ __('Manage labs & stations') }}</div>
                </a>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Pending') }}</div><div class="text-2xl font-bold">{{ $counts['pending'] ?? 0 }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('In Progress') }}</div><div class="text-2xl font-bold">{{ $counts['in_progress'] ?? 0 }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Completed') }}</div><div class="text-2xl font-bold">{{ $counts['completed'] ?? 0 }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Overdue') }}</div><div class="text-2xl font-bold {{ ($counts['overdue'] ?? 0) > 0 ? 'text-rose-600' : '' }}">{{ $counts['overdue'] ?? 0 }}</div></div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('In Progress Orders') }}</h2>
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($inProgress as $order)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <div class="font-medium"><a href="{{ route('dentaltrack.admin.orders.show', $order) }}" class="text-indigo-600 hover:underline">#{{ $order->id }}</a> - {{ $order->patient_ref ?? '-' }}</div>
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

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Pending Orders') }}</h2>
                    <div class="mt-4 space-y-2">
                        @forelse ($pending as $order)
                            <div class="flex items-center justify-between py-2 text-sm">
                                <span class="font-medium">#{{ $order->id }} - {{ $order->patient_ref ?? '-' }}</span>
                                <span class="text-xs uppercase text-slate-500">{{ $order->priority->value }}</span>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">{{ __('No pending orders.') }}</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Overdue Orders') }}</h2>
                    <div class="mt-4 space-y-2">
                        @forelse ($overdue as $order)
                            <div class="flex items-center justify-between py-2 text-sm">
                                <span class="font-medium text-rose-600">#{{ $order->id }} - {{ $order->patient_ref ?? '-' }}</span>
                                <span class="text-xs text-slate-500">{{ $order->due_date?->format('Y-m-d') }}</span>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">{{ __('No overdue orders.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Workstations') }}</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
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
@endsection
