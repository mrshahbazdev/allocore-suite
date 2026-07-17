@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.payments.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.payments.description') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="flex gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.payments.search_placeholder') }}" class="flex-1 rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Date') }}</th>
                    <th class="px-4 py-3">{{ __('Invoice') }}</th>
                    <th class="px-4 py-3">{{ __('Client') }}</th>
                    <th class="px-4 py-3">{{ __('Team') }}</th>
                    <th class="px-4 py-3">{{ __('Method') }}</th>
                    <th class="px-4 py-3">{{ __('Amount') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($payments as $payment)
                    <tr>
                        <td class="px-4 py-3 text-slate-900">{{ $payment->date?->format('d.m.Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $payment->invoice?->client?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $payment->team?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 capitalize">{{ $payment->payment_method }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $payment->invoice?->currency_symbol ?? '$' }}{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.payments.show', $payment) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">{{ __('admin.payments.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
@endsection
