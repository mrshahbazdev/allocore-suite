@extends('layouts.shell')

@section('title', __('Order #').$order->id)

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Order #') }}{{ $order->id }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('dentaltrack.admin.orders.sticker', $order) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Print Sticker') }}</a>
                <a href="{{ route('dentaltrack.admin.orders.edit', $order) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm grid gap-6 sm:grid-cols-2">
            <div>
                <div class="text-sm text-slate-500">{{ __('Patient Ref') }}</div>
                <div class="font-medium">{{ $order->patient_ref ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-slate-500">{{ __('Doctor') }}</div>
                <div class="font-medium">{{ $order->doctor_name ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-slate-500">{{ __('Company') }}</div>
                <div class="font-medium">{{ $order->company?->name }}</div>
            </div>
            <div>
                <div class="text-sm text-slate-500">{{ __('Lab') }}</div>
                <div class="font-medium">{{ $order->lab?->name }}</div>
            </div>
            <div>
                <div class="text-sm text-slate-500">{{ __('Product Type') }}</div>
                <div class="font-medium">{{ $order->productType?->name }}</div>
            </div>
            <div>
                <div class="text-sm text-slate-500">{{ __('Tracking Code') }}</div>
                <div class="font-medium font-mono">{{ $order->tracking_code }}</div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Progress') }}</h2>
            <div class="mt-4">
                <div class="flex justify-between text-sm font-medium text-slate-700 mb-1"><span>{{ __('Steps') }}</span><span>{{ $order->progressPercentage() }}%</span></div>
                <div class="h-2.5 w-full rounded-full bg-slate-200"><div class="h-2.5 rounded-full bg-indigo-600" style="width: {{ $order->progressPercentage() }}%"></div></div>
            </div>
            <div class="mt-4 space-y-2">
                @foreach ($order->steps as $step)
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-2">
                        <span class="text-sm">{{ $step->sort_order }}. {{ $step->step_name }}</span>
                        <span class="text-xs font-semibold capitalize {{ $step->status->value === 'done' ? 'text-emerald-600' : ($step->status->value === 'in_progress' ? 'text-indigo-600' : 'text-slate-500') }}">{{ str_replace('_', ' ', $step->status->value) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Scan History') }}</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Time') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Workstation') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Technician') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Event') }}</th><th class="px-3 py-2 text-left text-xs font-medium text-slate-500">{{ __('Duration') }}</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($order->scanEvents as $event)
                            <tr>
                                <td class="px-3 py-2 text-sm">{{ $event->scanned_at->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-2 text-sm">{{ $event->workstation?->name }}</td>
                                <td class="px-3 py-2 text-sm">{{ $event->user?->name }}</td>
                                <td class="px-3 py-2 text-sm capitalize">{{ str_replace('_', ' ', $event->event_type->value) }}</td>
                                <td class="px-3 py-2 text-sm">{{ $event->formattedDuration() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-4 text-sm text-slate-500">{{ __('No scan events.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Rework / Quality') }}</h2>
            <div class="mt-4 space-y-2">
                @forelse ($order->reworkEvents as $rework)
                    <div class="rounded-lg border border-slate-200 p-3">
                        <div class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $rework->cause->value)) }} - {{ str_replace('_', ' ', $rework->status->value) }}</div>
                        <div class="text-sm text-slate-500">{{ $rework->description }}</div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('No rework events.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
