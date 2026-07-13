<div>
    @include('invoicemaker::partials.nav')

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('InvoiceMaker') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Invoice and cash-flow overview') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Financial operations for :team.', ['team' => auth()->user()->currentTeam->name]) }}</p>
        </div>
        <a href="{{ route('invoicemaker.invoices.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Create invoice') }}</a>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            __('Revenue') => number_format((float) $stats['revenue'], 2),
            __('Outstanding') => number_format((float) $stats['outstanding'], 2),
            __('Expenses') => number_format((float) $stats['expenses'], 2),
            __('Net profit') => number_format((float) $stats['profit'], 2),
            __('Invoices') => $stats['total_invoices'],
            __('Paid') => $stats['paid_invoices'],
            __('Overdue') => $stats['overdue'],
            __('Clients') => $stats['clients'],
        ] as $label => $value)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">{{ __('Recent invoices') }}</h2>
            <a href="{{ route('invoicemaker.invoices.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('View all') }}</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Number') }}</th><th class="px-5 py-3">{{ __('Client') }}</th><th class="px-5 py-3">{{ __('Date') }}</th><th class="px-5 py-3">{{ __('Status') }}</th><th class="px-5 py-3 text-right">{{ __('Total') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($recentInvoices as $invoice)
                    <tr>
                        <td class="px-5 py-4"><a class="font-medium text-indigo-600 hover:underline" href="{{ route('invoicemaker.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                        <td class="px-5 py-4 text-slate-600">{{ $invoice->client->company_name ?: $invoice->client->name }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $invoice->invoice_date->format('M d, Y') }}</td>
                        <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">{{ ucfirst($invoice->status) }}</span></td>
                        <td class="px-5 py-4 text-right font-medium">{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->grand_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">{{ __('No invoices yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
