<div>
    @include('invoicemaker::partials.nav')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6"><h1 class="text-2xl font-bold text-slate-900">{{ $product ? __('Edit product') : __('Add product') }}</h1><p class="text-sm text-slate-500">{{ __('Pricing, tax, purchasing cost, and stock details.') }}</p></div>
        <form wire:submit="save" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ([
                    'name' => __('Name'),
                    'price' => __('Sale price'),
                    'purchase_price' => __('Purchase price'),
                    'unit' => __('Unit'),
                    'tax_rate' => __('Tax rate %'),
                    'stock_quantity' => __('Stock quantity'),
                ] as $field => $label)
                    <label class="block"><span class="text-sm font-medium text-slate-700">{{ $label }}</span><input wire:model="{{ $field }}" type="{{ in_array($field, ['price', 'purchase_price', 'tax_rate', 'stock_quantity']) ? 'number' : 'text' }}" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error($field)<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                @endforeach
            </div>
            <label class="block"><span class="text-sm font-medium text-slate-700">{{ __('Description') }}</span><textarea wire:model="description" rows="4" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea></label>
            <label class="flex items-center gap-2 text-sm text-slate-700"><input wire:model="manage_stock" type="checkbox" class="rounded border-slate-300 text-indigo-600"> {{ __('Track inventory') }}</label>
            <div class="flex justify-end gap-3"><a href="{{ route('invoicemaker.products.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Cancel') }}</a><button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save product') }}</button></div>
        </form>
    </div>
</div>
