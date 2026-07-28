@extends('layouts.shell')

@section('title', __('Order #').$order->id)

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Order #') }}{{ $order->id }}</h1>
                <p class="text-sm text-slate-500">{{ $order->tracking_code }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dentaltrack.admin.orders.sticker', $order) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Print Sticker') }}</a>
                <a href="{{ route('dentaltrack.admin.orders.edit', $order) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('dentaltrack.admin.orders.destroy', $order) }}" class="inline" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="rounded-lg bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-200">{{ __('Delete') }}</button></form>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Status') }}</h2>
            <form method="POST" action="{{ route('dentaltrack.admin.orders.update', $order) }}" class="mt-3 flex flex-wrap items-end gap-3">
                @csrf @method('PUT')
                <input type="hidden" name="dentaltrack_company_id" value="{{ $order->dentaltrack_company_id }}">
                <input type="hidden" name="dentaltrack_lab_id" value="{{ $order->dentaltrack_lab_id }}">
                <input type="hidden" name="dentaltrack_product_type_id" value="{{ $order->dentaltrack_product_type_id }}">
                <input type="hidden" name="priority" value="{{ $order->priority->value }}">
                <select name="status" class="rounded-lg border-slate-300 text-sm">
                    @foreach (['pending','in_progress','completed','cancelled','on_hold'] as $s)
                        <option value="{{ $s }}" {{ $order->status->value === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">{{ __('Update Status') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
            <div class="sm:col-span-2 lg:col-span-1">
                <div class="text-sm text-slate-500">{{ __('Order QR') }}</div>
                <div class="mt-2 flex flex-col items-start gap-2">
                    <div class="rounded border border-slate-200 p-2 bg-white">{!! $qrSvg !!}</div>
                    <a href="{{ $order->trackUrl() }}" target="_blank" class="text-xs text-indigo-600 hover:underline">{{ __('Public track link') }}</a>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Progress') }}</h2>
            <div class="mt-4">
                <div class="flex justify-between text-sm font-medium text-slate-700 mb-1"><span>{{ __('Steps') }}</span><span>{{ $order->progressPercentage() }}%</span></div>
                <div class="h-2.5 w-full rounded-full bg-slate-200"><div class="h-2.5 rounded-full bg-indigo-600" style="width: {{ $order->progressPercentage() }}%"></div></div>
            </div>
            <div class="mt-4 space-y-3">
                @foreach ($order->steps as $step)
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">{{ $step->sort_order }}. {{ $step->step_name }}</span>
                            <span class="text-xs font-semibold capitalize {{ $step->status->value === 'done' ? 'text-emerald-600' : ($step->status->value === 'in_progress' ? 'text-indigo-600' : 'text-slate-500') }}">{{ str_replace('_', ' ', $step->status->value) }}</span>
                        </div>
                        <form method="POST" action="{{ route('dentaltrack.admin.orders.steps.update', [$order, $step]) }}" class="mt-3 flex flex-wrap items-end gap-2">
                            @csrf @method('PUT')
                            <div>
                                <label class="text-xs text-slate-500">{{ __('Status') }}</label>
                                <select name="status" class="rounded-lg border-slate-300 text-xs">
                                    @foreach (['pending','in_progress','done','skipped'] as $s)
                                        <option value="{{ $s }}" {{ $step->status->value === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">{{ __('Technician') }}</label>
                                <select name="assigned_to" class="rounded-lg border-slate-300 text-xs">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}" {{ $step->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="rounded-lg bg-slate-900 px-3 py-1 text-xs font-semibold text-white hover:bg-slate-700">{{ __('Update') }}</button>
                        </form>
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
