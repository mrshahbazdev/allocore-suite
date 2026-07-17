@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Payment') }}</h1>
            <p class="text-sm text-slate-500">{{ $payment->transaction_reference ?? '—' }}</p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to payments') }}</a>
    </div>

    <div class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Invoice') }}</dt><dd class="font-medium text-slate-900">{{ $payment->invoice?->invoice_number ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Client') }}</dt><dd class="font-medium text-slate-900">{{ $payment->invoice?->client?->name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Team') }}</dt><dd class="font-medium text-slate-900">{{ $payment->team?->name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Date') }}</dt><dd class="font-medium text-slate-900">{{ $payment->date?->format('d.m.Y') ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Method') }}</dt><dd class="font-medium text-slate-900 capitalize">{{ $payment->payment_method }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Amount') }}</dt><dd class="font-medium text-slate-900">{{ $payment->invoice?->currency_symbol ?? '$' }}{{ number_format($payment->amount, 2) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Reference') }}</dt><dd class="font-medium text-slate-900">{{ $payment->transaction_reference ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Notes') }}</dt><dd class="font-medium text-slate-900">{{ $payment->notes ?? '—' }}</dd></div>
        </dl>
    </div>
@endsection
