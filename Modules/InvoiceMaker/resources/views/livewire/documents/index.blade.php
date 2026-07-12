<div>
    @include('invoicemaker::partials.nav')
    @php($isEstimate = $type === \Modules\InvoiceMaker\Models\Invoice::TYPE_ESTIMATE)
    <div class="mb-6 flex items-end justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-slate-900">{{ $isEstimate ? __('Estimates') : __('Invoices') }}</h1><p class="text-sm text-slate-500">{{ $isEstimate ? __('Create proposals and convert accepted estimates.') : __('Track drafts, outstanding balances, payments, and recurring billing.') }}</p></div>
        <a href="{{ route($isEstimate ? 'invoicemaker.estimates.create' : 'invoicemaker.invoices.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ $isEstimate ? __('Create estimate') : __('Create invoice') }}</a>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('Search number or client') }}" class="w-full rounded-lg border-slate-300 text-sm sm:max-w-sm">
            <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm"><option value="">{{ __('All statuses') }}</option>@foreach (['draft', 'sent', 'paid', 'overdue', 'cancelled'] as $value)<option value="{{ $value }}">{{ ucfirst($value) }}</option>@endforeach</select>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('Number') }}</th><th class="px-5 py-3">{{ __('Client') }}</th><th class="px-5 py-3">{{ __('Due') }}</th><th class="px-5 py-3">{{ __('Status') }}</th><th class="px-5 py-3 text-right">{{ __('Amount') }}</th><th class="px-5 py-3 text-right">{{ __('Actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($documents as $document)
                    <tr>
                        <td class="px-5 py-4"><a href="{{ route($isEstimate ? 'invoicemaker.estimates.show' : 'invoicemaker.invoices.show', $document) }}" class="font-medium text-indigo-600 hover:underline">{{ $document->invoice_number }}</a>@if($document->is_recurring)<span class="ml-2 rounded bg-violet-100 px-2 py-0.5 text-xs text-violet-700">{{ __('Recurring') }}</span>@endif</td>
                        <td class="px-5 py-4 text-slate-600">{{ $document->client->company_name ?: $document->client->name }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $document->due_date->format('M d, Y') }}</td>
                        <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">{{ ucfirst($document->status) }}</span></td>
                        <td class="px-5 py-4 text-right font-medium">{{ $document->currency_symbol }}{{ number_format((float) $document->grand_total, 2) }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-right">
                            <a href="{{ route($isEstimate ? 'invoicemaker.estimates.edit' : 'invoicemaker.invoices.edit', $document) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                            @if($document->status === 'draft')<button wire:click="markSent({{ $document->id }})" class="ml-3 text-emerald-600 hover:underline">{{ __('Mark sent') }}</button>@endif
                            @if($isEstimate)<button wire:click="convertToInvoice({{ $document->id }})" class="ml-3 text-violet-600 hover:underline">{{ __('Convert') }}</button>@endif
                            <button wire:click="duplicate({{ $document->id }})" class="ml-3 text-slate-600 hover:underline">{{ __('Duplicate') }}</button>
                            <button wire:click="delete({{ $document->id }})" wire:confirm="{{ __('Delete this document?') }}" class="ml-3 text-rose-600 hover:underline">{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">{{ __('No matching documents.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">{{ $documents->links() }}</div>
    </div>
</div>
