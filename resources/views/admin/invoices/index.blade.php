@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.invoices.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.invoices.description') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="flex flex-wrap items-center gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.invoices.search_placeholder') }}" class="flex-1 rounded-lg border-slate-300 text-sm min-w-[200px]">
            <select name="status" class="rounded-lg border-slate-300 text-sm">
                <option value="">{{ __('admin.invoices.all_statuses') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Invoice') }}</th>
                    <th class="px-4 py-3">{{ __('Client') }}</th>
                    <th class="px-4 py-3">{{ __('Team') }}</th>
                    <th class="px-4 py-3">{{ __('Date') }}</th>
                    <th class="px-4 py-3">{{ __('Due') }}</th>
                    <th class="px-4 py-3">{{ __('Total') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $invoice->invoice_number }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $invoice->client?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $invoice->team?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $invoice->invoice_date?->format('d.m.Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $invoice->due_date?->format('d.m.Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $invoice->currency_symbol }}{{ number_format($invoice->grand_total, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($invoice->status === 'overdue' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600') }}">
                                {{ $invoice->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-slate-400">{{ __('admin.invoices.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
@endsection
