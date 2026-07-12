<div>
    @include('invoicemaker::partials.nav')
    <div class="mb-6 flex items-end justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-slate-900">{{ __('Products & services') }}</h1><p class="text-sm text-slate-500">{{ __('Reusable line items with prices, taxes, and stock.') }}</p></div>
        <a href="{{ route('invoicemaker.products.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Add product') }}</a>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4"><input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('Search products') }}" class="w-full max-w-sm rounded-lg border-slate-300 text-sm"></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('Product') }}</th><th class="px-5 py-3">{{ __('Price') }}</th><th class="px-5 py-3">{{ __('Tax') }}</th><th class="px-5 py-3">{{ __('Stock') }}</th><th class="px-5 py-3 text-right">{{ __('Actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-5 py-4"><p class="font-medium text-slate-900">{{ $product->name }}</p><p class="max-w-md truncate text-xs text-slate-500">{{ $product->description }}</p></td>
                        <td class="px-5 py-4 text-slate-600">{{ number_format((float) $product->price, 2) }} / {{ $product->unit }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ number_format((float) $product->tax_rate, 2) }}%</td>
                        <td class="px-5 py-4 text-slate-600">{{ $product->manage_stock ? $product->stock_quantity : __('Not tracked') }}</td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('invoicemaker.products.edit', $product) }}" class="font-medium text-indigo-600 hover:underline">{{ __('Edit') }}</a> <button wire:click="delete({{ $product->id }})" wire:confirm="{{ __('Delete this product?') }}" class="ml-3 text-rose-600 hover:underline">{{ __('Delete') }}</button></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">{{ __('No products found.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">{{ $products->links() }}</div>
    </div>
</div>
