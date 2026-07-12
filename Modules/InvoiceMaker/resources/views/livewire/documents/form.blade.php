<div>
    @include('invoicemaker::partials.nav')
    @php($isEstimate = $type === \Modules\InvoiceMaker\Models\Invoice::TYPE_ESTIMATE)
    @php($totals = $this->totals)
    <div class="mb-6"><h1 class="text-2xl font-bold text-slate-900">{{ $invoice ? __('Edit') : __('Create') }} {{ $isEstimate ? __('estimate') : __('invoice') }}</h1><p class="text-sm text-slate-500">{{ __('Build the document, apply taxes and discounts, and configure recurring delivery.') }}</p></div>
    <form wire:submit="save" class="space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <label class="block lg:col-span-2"><span class="text-sm font-medium text-slate-700">{{ __('Client') }}</span><select wire:model="client_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">{{ __('Select client') }}</option>@foreach($this->clients as $client)<option value="{{ $client->id }}">{{ $client->company_name ?: $client->name }}</option>@endforeach</select>@error('client_id')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-sm font-medium text-slate-700">{{ __('Date') }}</span><input wire:model="invoice_date" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                <label class="block"><span class="text-sm font-medium text-slate-700">{{ $isEstimate ? __('Valid until') : __('Due date') }}</span><input wire:model="due_date" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                <label class="block lg:col-span-2"><span class="text-sm font-medium text-slate-700">{{ __('Template') }}</span><select wire:model="template_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">{{ __('No template') }}</option>@foreach($this->templates as $template)<option value="{{ $template->id }}">{{ $template->name }}</option>@endforeach</select></label>
                <label class="block"><span class="text-sm font-medium text-slate-700">{{ __('Currency') }}</span><input wire:model="currency" maxlength="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                <label class="block"><span class="text-sm font-medium text-slate-700">{{ __('Document discount') }}</span><input wire:model.live.debounce.300ms="discount" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><h2 class="font-semibold text-slate-900">{{ __('Line items') }}</h2><button type="button" wire:click="addItem" class="rounded-lg border border-indigo-200 px-3 py-2 text-sm font-medium text-indigo-600">{{ __('Add line') }}</button></div>
            <div class="space-y-4 p-5">
                @foreach($items as $index => $item)
                    <div wire:key="item-{{ $index }}" class="grid gap-3 rounded-lg border border-slate-200 p-4 lg:grid-cols-12">
                        <label class="lg:col-span-3"><span class="text-xs font-medium text-slate-500">{{ __('Product') }}</span><select wire:model="items.{{ $index }}.product_id" wire:change="selectProduct({{ $index }})" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">{{ __('Custom item') }}</option>@foreach($this->products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select></label>
                        <label class="lg:col-span-3"><span class="text-xs font-medium text-slate-500">{{ __('Description') }}</span><input wire:model="items.{{ $index }}.description" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error("items.$index.description")<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="lg:col-span-1"><span class="text-xs font-medium text-slate-500">{{ __('Qty') }}</span><input wire:model.live.debounce.300ms="items.{{ $index }}.quantity" type="number" min="0.01" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                        <label class="lg:col-span-2"><span class="text-xs font-medium text-slate-500">{{ __('Price') }}</span><input wire:model.live.debounce.300ms="items.{{ $index }}.unit_price" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                        <label class="lg:col-span-1"><span class="text-xs font-medium text-slate-500">{{ __('Tax %') }}</span><input wire:model.live.debounce.300ms="items.{{ $index }}.tax_rate" type="number" min="0" max="100" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                        <label class="lg:col-span-1"><span class="text-xs font-medium text-slate-500">{{ __('Discount') }}</span><input wire:model.live.debounce.300ms="items.{{ $index }}.discount" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                        <div class="flex items-end justify-end lg:col-span-1"><button type="button" wire:click="removeItem({{ $index }})" class="rounded-lg px-3 py-2 text-sm text-rose-600">{{ __('Remove') }}</button></div>
                    </div>
                @endforeach
                @error('items')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 text-sm sm:ml-auto sm:max-w-sm">
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span>{{ number_format($totals['subtotal'], 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Tax') }}</span><span>{{ number_format($totals['tax_total'], 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Discount') }}</span><span>-{{ number_format($totals['discount'], 2) }}</span></div>
                <div class="flex justify-between border-t border-slate-300 pt-2 text-lg font-bold"><span>{{ __('Total') }}</span><span>{{ $currency }} {{ number_format($totals['grand_total'], 2) }}</span></div>
            </div>
        </section>

        <section class="grid gap-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:grid-cols-2">
            <label class="block"><span class="text-sm font-medium text-slate-700">{{ __('Notes') }}</span><textarea wire:model="notes" rows="4" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea></label>
            <label class="block"><span class="text-sm font-medium text-slate-700">{{ __('Payment terms') }}</span><textarea wire:model="payment_terms" rows="4" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea></label>
            @unless($isEstimate)
                <label class="flex items-center gap-2 text-sm text-slate-700"><input wire:model.live="is_recurring" type="checkbox" class="rounded border-slate-300 text-indigo-600"> {{ __('Recurring invoice') }}</label>
                @if($is_recurring)<label class="block"><span class="text-sm font-medium text-slate-700">{{ __('Frequency') }}</span><select wire:model="recurring_frequency" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@foreach(['weekly', 'monthly', 'quarterly', 'yearly'] as $frequency)<option value="{{ $frequency }}">{{ ucfirst($frequency) }}</option>@endforeach</select></label>@endif
            @endunless
        </section>

        <div class="flex justify-end gap-3"><a href="{{ route($isEstimate ? 'invoicemaker.estimates.index' : 'invoicemaker.invoices.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Cancel') }}</a><button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white">{{ __('Save document') }}</button></div>
    </form>
</div>
