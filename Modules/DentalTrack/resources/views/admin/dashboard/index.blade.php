@extends('layouts.shell')

@section('title', __('DentalTrack Admin'))

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('DentalTrack Admin') }}</h1>
            <a href="{{ route('dentaltrack.scan.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Open Scanner') }}</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Total') }}</div><div class="text-2xl font-bold">{{ $counts['total'] ?? 0 }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Pending') }}</div><div class="text-2xl font-bold">{{ $counts['pending'] ?? 0 }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('In Progress') }}</div><div class="text-2xl font-bold">{{ $counts['in_progress'] ?? 0 }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Completed') }}</div><div class="text-2xl font-bold">{{ $counts['completed'] ?? 0 }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Overdue') }}</div><div class="text-2xl font-bold text-rose-600">{{ $counts['overdue'] ?? 0 }}</div></div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Live Order Board') }}</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Order') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Company / Lab') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Priority') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Status') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Due') }}</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="px-3 py-2 text-sm font-medium"><a href="{{ route('dentaltrack.admin.orders.show', $order) }}" class="text-indigo-600 hover:underline">#{{ $order->id }}</a><div class="text-xs text-slate-500">{{ $order->patient_ref }}</div></td>
                                    <td class="px-3 py-2 text-sm">{{ $order->company?->name }}<div class="text-xs text-slate-500">{{ $order->lab?->name }}</div></td>
                                    <td class="px-3 py-2 text-sm capitalize">{{ $order->priority->value }}</td>
                                    <td class="px-3 py-2 text-sm capitalize">{{ str_replace('_', ' ', $order->status->value) }}</td>
                                    <td class="px-3 py-2 text-sm">{{ $order->due_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-4 text-sm text-slate-500">{{ __('No active orders.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $orders->links() }}</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('AI Suggestions') }}</h2>
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

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Scans') }}</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Time') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Order') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Workstation') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Technician') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Event') }}</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentScans as $event)
                            <tr>
                                <td class="px-3 py-2 text-sm">{{ $event->scanned_at->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-2 text-sm">#{{ $event->order?->id }}</td>
                                <td class="px-3 py-2 text-sm">{{ $event->workstation?->name }}</td>
                                <td class="px-3 py-2 text-sm">{{ $event->user?->name }}</td>
                                <td class="px-3 py-2 text-sm capitalize">{{ str_replace('_', ' ', $event->event_type->value) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-4 text-sm text-slate-500">{{ __('No scans yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
