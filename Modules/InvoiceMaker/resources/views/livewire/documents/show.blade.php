<div>
    @include('invoicemaker::partials.nav')
    @php($isEstimate = $invoice->isEstimate())
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ $isEstimate ? __('Estimate') : __('Invoice') }}</p><h1 class="text-2xl font-bold text-slate-900">{{ $invoice->invoice_number }}</h1><p class="text-sm text-slate-500">{{ $invoice->client->company_name ?: $invoice->client->name }} · {{ ucfirst($invoice->status) }}</p></div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route($isEstimate ? 'invoicemaker.estimates.edit' : 'invoicemaker.invoices.edit', $invoice) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Edit') }}</a>
            <a href="{{ route('invoicemaker.invoices.preview', $invoice) }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Preview PDF') }}</a>
            <a href="{{ route('invoicemaker.invoices.download', $invoice) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Download') }}</a>
            @if($invoice->status === 'draft')<button wire:click="markSent" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Mark sent') }}</button>@endif
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="space-y-6 xl:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6 grid gap-4 sm:grid-cols-3"><div><p class="text-xs uppercase text-slate-500">{{ __('Issue date') }}</p><p class="font-medium">{{ $invoice->invoice_date->format('M d, Y') }}</p></div><div><p class="text-xs uppercase text-slate-500">{{ __('Due date') }}</p><p class="font-medium">{{ $invoice->due_date->format('M d, Y') }}</p></div><div><p class="text-xs uppercase text-slate-500">{{ __('Public link') }}</p><a href="{{ $publicUrl }}" target="_blank" class="break-all text-sm text-indigo-600 hover:underline">{{ __('Open client view') }}</a></div></div>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">{{ __('Description') }}</th><th class="px-4 py-3 text-right">{{ __('Qty') }}</th><th class="px-4 py-3 text-right">{{ __('Price') }}</th><th class="px-4 py-3 text-right">{{ __('Tax') }}</th><th class="px-4 py-3 text-right">{{ __('Total') }}</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach($invoice->items as $item)<tr><td class="px-4 py-3">{{ $item->description }}</td><td class="px-4 py-3 text-right">{{ $item->quantity }}</td><td class="px-4 py-3 text-right">{{ number_format((float) $item->unit_price, 2) }}</td><td class="px-4 py-3 text-right">{{ number_format((float) $item->tax_rate, 2) }}%</td><td class="px-4 py-3 text-right font-medium">{{ number_format((float) $item->total, 2) }}</td></tr>@endforeach</tbody></table></div>
                <div class="ml-auto mt-5 max-w-sm space-y-2 text-sm"><div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span>{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->subtotal, 2) }}</span></div><div class="flex justify-between"><span class="text-slate-500">{{ __('Tax') }}</span><span>{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->tax_total, 2) }}</span></div><div class="flex justify-between"><span class="text-slate-500">{{ __('Discount') }}</span><span>-{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->discount, 2) }}</span></div><div class="flex justify-between border-t border-slate-200 pt-2 text-lg font-bold"><span>{{ __('Total') }}</span><span>{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->grand_total, 2) }}</span></div><div class="flex justify-between text-emerald-700"><span>{{ __('Paid') }}</span><span>{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->amount_paid, 2) }}</span></div><div class="flex justify-between font-semibold text-rose-700"><span>{{ __('Due') }}</span><span>{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->amount_due, 2) }}</span></div></div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 font-semibold text-slate-900">{{ __('Comments') }}</h2>
                <div class="mb-5 space-y-3">@forelse($invoice->comments as $entry)<div class="rounded-lg bg-slate-50 p-3"><div class="flex justify-between text-xs text-slate-500"><span>{{ $entry->author_name ?: $entry->user?->name }}</span><span>{{ $entry->created_at->diffForHumans() }}</span></div><p class="mt-1 text-sm text-slate-700">{{ $entry->comment }}</p></div>@empty<p class="text-sm text-slate-500">{{ __('No comments.') }}</p>@endforelse</div>
                <form wire:submit="addComment" class="flex gap-2"><input wire:model="comment" class="flex-1 rounded-lg border-slate-300 text-sm" placeholder="{{ __('Add an internal comment') }}"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">{{ __('Add') }}</button></form>
            </div>
        </section>

        <aside class="space-y-6">
            @unless($isEstimate)
                <form wire:submit="addPayment" class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-slate-900">{{ __('Record payment') }}</h2>
                    <label class="block"><span class="text-sm text-slate-600">{{ __('Amount') }}</span><input wire:model="payment_amount" type="number" min="0.01" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('payment_amount')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                    <label class="block"><span class="text-sm text-slate-600">{{ __('Method') }}</span><select wire:model="payment_method" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@foreach(['bank_transfer', 'credit_card', 'cash', 'check', 'paypal', 'stripe'] as $method)<option value="{{ $method }}">{{ ucwords(str_replace('_', ' ', $method)) }}</option>@endforeach</select></label>
                    <label class="block"><span class="text-sm text-slate-600">{{ __('Date') }}</span><input wire:model="payment_date" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                    <label class="block"><span class="text-sm text-slate-600">{{ __('Notes') }}</span><textarea wire:model="payment_notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea></label>
                    <button @disabled($invoice->amount_due <= 0) class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">{{ __('Record payment') }}</button>
                </form>
            @endunless
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold text-slate-900">{{ __('Client') }}</h2><p class="mt-3 font-medium">{{ $invoice->client->name }}</p><p class="text-sm text-slate-500">{{ $invoice->client->company_name }}</p><p class="mt-2 text-sm text-slate-600">{{ $invoice->client->email }}</p><p class="whitespace-pre-line text-sm text-slate-600">{{ $invoice->client->address }}</p></div>
        </aside>
    </div>
</div>
