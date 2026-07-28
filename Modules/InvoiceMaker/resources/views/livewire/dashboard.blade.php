<div class="max-w-7xl mx-auto space-y-8">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('InvoiceMaker') }}</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Invoice and cash-flow overview') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Financial operations for :team.', ['team' => auth()->user()->currentTeam->name]) }}</p>
        </div>
        <a href="{{ route('invoicemaker.invoices.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Create invoice') }}</a>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => __('Revenue'), 'value' => number_format((float) $stats['revenue'], 2), 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.03-.659-1.171-.879-1.172-2.303 0-3.182 1.171-.879 3.07-.879 4.242 0L12 6', 'color' => 'text-emerald-500'],
            ['label' => __('Outstanding'), 'value' => number_format((float) $stats['outstanding'], 2), 'icon' => 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941', 'color' => 'text-rose-500'],
            ['label' => __('Expenses'), 'value' => number_format((float) $stats['expenses'], 2), 'icon' => 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-6.18 2.845l-3.241 3.24a1.5 1.5 0 01-2.122 0l-1.06-1.06a1.5 1.5 0 010-2.122l3.24-3.24', 'color' => 'text-amber-500'],
            ['label' => __('Net profit'), 'value' => number_format((float) $stats['profit'], 2), 'icon' => 'M21 12a9 9 0 11-18 0 9 9 0 0118 0z M9 12.75l2.25 2.25L15 9.75', 'color' => 'text-indigo-500'],
            ['label' => __('Invoices'), 'value' => $stats['total_invoices'], 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', 'color' => 'text-slate-500'],
            ['label' => __('Paid'), 'value' => $stats['paid_invoices'], 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-emerald-500'],
            ['label' => __('Overdue'), 'value' => $stats['overdue'], 'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z', 'color' => 'text-rose-500'],
            ['label' => __('Clients'), 'value' => $stats['clients'], 'icon' => 'M15 19.128a9.38 9.38 0 002.625.364 11.978 11.978 0 00-2.625-.364C10.444 18.626 7.647 17.13 6 15m12 0c-.816.78-1.85 1.24-3 1.382A9.38 9.38 0 0012.375 15c-2.474 0-4.75.81-6.527 2.181C4.178 17.61 3.5 17.107 3.5 16.5v-.75a2.25 2.25 0 012.25-2.25h12a2.25 2.25 0 012.25 2.25v.75c0 .607-.678 1.11-1.348.681A9.38 9.38 0 0015 19.128z M12 11.25a3 3 0 100-6 3 3 0 000 6z', 'color' => 'text-indigo-500'],
        ] as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="text-xs uppercase text-slate-500">{{ $card['label'] }}</div>
                    <svg class="h-5 w-5 {{ $card['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                </div>
                <div class="mt-1 text-2xl font-bold text-slate-900">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent invoices') }}</h2>
            <a href="{{ route('invoicemaker.invoices.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ __('View all') }}</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-6 py-3">{{ __('Number') }}</th><th class="px-6 py-3">{{ __('Client') }}</th><th class="px-6 py-3">{{ __('Date') }}</th><th class="px-6 py-3">{{ __('Status') }}</th><th class="px-6 py-3 text-right">{{ __('Total') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentInvoices as $invoice)
                        @php($statusClass = match($invoice->status) {
                            'paid' => 'bg-emerald-100 text-emerald-700',
                            'sent' => 'bg-indigo-100 text-indigo-700',
                            'overdue' => 'bg-rose-100 text-rose-700',
                            'draft' => 'bg-slate-100 text-slate-700',
                            'cancelled' => 'bg-slate-100 text-slate-500',
                            default => 'bg-slate-100 text-slate-700',
                        })
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4"><a class="font-semibold text-indigo-600 hover:text-indigo-500" href="{{ route('invoicemaker.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                            <td class="px-6 py-4 text-slate-600">{{ $invoice->client->company_name ?: $invoice->client->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $invoice->invoice_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">{{ ucfirst($invoice->status) }}</span></td>
                            <td class="px-6 py-4 text-right font-medium">{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->grand_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">{{ __('No invoices yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
