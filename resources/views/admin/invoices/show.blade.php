@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Invoice') }} #{{ $invoice->invoice_number }}</h1>
            <p class="text-sm text-slate-500">{{ $invoice->uuid }}</p>
        </div>
        <a href="{{ route('admin.invoices.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to invoices') }}</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Details') }}</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Client') }}</dt><dd class="font-medium text-slate-900">{{ $invoice->client?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Team') }}</dt><dd class="font-medium text-slate-900">{{ $invoice->team?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Type') }}</dt><dd class="font-medium text-slate-900 capitalize">{{ $invoice->type }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Status') }}</dt><dd class="font-medium text-slate-900 capitalize">{{ $invoice->status }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Invoice date') }}</dt><dd class="font-medium text-slate-900">{{ $invoice->invoice_date?->format('d.m.Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Due date') }}</dt><dd class="font-medium text-slate-900">{{ $invoice->due_date?->format('d.m.Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Subtotal') }}</dt><dd class="font-medium text-slate-900">{{ $invoice->currency_symbol }}{{ number_format($invoice->subtotal, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Tax') }}</dt><dd class="font-medium text-slate-900">{{ $invoice->currency_symbol }}{{ number_format($invoice->tax_total, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Grand total') }}</dt><dd class="font-medium text-slate-900">{{ $invoice->currency_symbol }}{{ number_format($invoice->grand_total, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Amount paid') }}</dt><dd class="font-medium text-slate-900">{{ $invoice->currency_symbol }}{{ number_format($invoice->amount_paid, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Amount due') }}</dt><dd class="font-medium text-slate-900">{{ $invoice->currency_symbol }}{{ number_format($invoice->amount_due, 2) }}</dd></div>
            </dl>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Items') }}</h2>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('Description') }}</th>
                            <th class="px-4 py-3">{{ __('Qty') }}</th>
                            <th class="px-4 py-3">{{ __('Price') }}</th>
                            <th class="px-4 py-3">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($invoice->items as $item)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $item->description }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $invoice->currency_symbol }}{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $invoice->currency_symbol }}{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">{{ __('No items.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Payments') }}</h2>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('Date') }}</th>
                            <th class="px-4 py-3">{{ __('Method') }}</th>
                            <th class="px-4 py-3">{{ __('Amount') }}</th>
                            <th class="px-4 py-3">{{ __('Reference') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($invoice->payments as $payment)
                            <tr>
                                <td class="px-4 py-3 text-slate-900">{{ $payment->date?->format('d.m.Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 capitalize">{{ $payment->method }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $invoice->currency_symbol }}{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $payment->reference ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">{{ __('No payments.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
