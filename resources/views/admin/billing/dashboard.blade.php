@extends('layouts.shell')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Billing Dashboard') }}</h1>
    <p class="text-sm text-slate-500">{{ __('Platform revenue, invoices, payments and subscriptions at a glance.') }}</p>
</div>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
        <div class="text-sm text-slate-500">{{ __('Total revenue') }}</div>
        <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($totalRevenue, 2) }}</div>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
        <div class="text-sm text-slate-500">{{ __('Outstanding') }}</div>
        <div class="mt-1 text-2xl font-bold text-rose-600">{{ number_format($outstanding, 2) }}</div>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
        <div class="text-sm text-slate-500">{{ __('Overdue invoices') }}</div>
        <div class="mt-1 text-2xl font-bold text-amber-600">{{ $overdue }}</div>
        <div class="text-xs text-slate-400">{{ number_format($overdueAmount, 2) }}</div>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
        <div class="text-sm text-slate-500">{{ __('MRR / ARR') }}</div>
        <div class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format($mrr, 2) }}</div>
        <div class="text-xs text-slate-400">{{ number_format($arr, 2) }}</div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3 mb-6">
    <div class="lg:col-span-2 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Monthly revenue') }}</h2>
        <canvas id="revenueChart" height="120"></canvas>
    </div>
    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Invoice status') }}</h2>
        <canvas id="statusChart" height="200"></canvas>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3 mb-6">
    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 overflow-x-auto">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Recent invoices') }}</h2>
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-500 uppercase border-b"><tr><th>{{ __('Invoice') }}</th><th>{{ __('Status') }}</th><th class="text-right">{{ __('Total') }}</th></tr></thead>
            <tbody>
                @forelse ($recentInvoices as $invoice)
                    <tr class="border-b border-slate-100">
                        <td class="py-2">{{ $invoice->invoice_number }}</td>
                        <td class="py-2"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($invoice->status === 'overdue' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">{{ $invoice->status }}</span></td>
                        <td class="py-2 text-right">{{ number_format($invoice->grand_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-slate-500">{{ __('No invoices yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 overflow-x-auto">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Recent payments') }}</h2>
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-500 uppercase border-b"><tr><th>{{ __('Date') }}</th><th>{{ __('Method') }}</th><th class="text-right">{{ __('Amount') }}</th></tr></thead>
            <tbody>
                @forelse ($recentPayments as $payment)
                    <tr class="border-b border-slate-100">
                        <td class="py-2">{{ $payment->date?->format('Y-m-d') }}</td>
                        <td class="py-2">{{ $payment->payment_method }}</td>
                        <td class="py-2 text-right">{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-slate-500">{{ __('No payments yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 overflow-x-auto">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Recent subscriptions') }}</h2>
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-500 uppercase border-b"><tr><th>{{ __('Plan') }}</th><th>{{ __('Status') }}</th><th class="text-right">{{ __('Total') }}</th></tr></thead>
            <tbody>
                @forelse ($subscriptions as $sub)
                    <tr class="border-b border-slate-100">
                        <td class="py-2">{{ $sub->plan?->name }}</td>
                        <td class="py-2"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $sub->status }}</span></td>
                        <td class="py-2 text-right">{{ number_format($sub->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-slate-500">{{ __('No subscriptions yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: '{{ __("Revenue") }}',
                data: {!! json_encode($revenueSeries) !!},
                backgroundColor: '#4f46e5',
                borderRadius: 4,
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys({!! json_encode($statusCounts) !!}),
            datasets: [{
                data: Object.values({!! json_encode($statusCounts) !!}),
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#94a3b8', '#6366f1'],
            }]
        },
        options: { responsive: true }
    });
</script>
@endpush
@endsection
