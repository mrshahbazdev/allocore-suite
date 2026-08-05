@php $title = __('Products'); @endphp

<div>
 <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <div>
 <h2 class="text-2xl font-bold text-txmain">{{ __('Products') }}</h2>
 <p class="text-txmain">{{ __('Manage your product library') }}</p>
 </div>
 <a href="{{ route('invoicemaker.products.create') }}"
 class="bg-brand-600 text-white py-2 px-4 rounded-lg hover:bg-brand-700 transition duration-200 text-center">
 + {{ __('Add Product') }}
 </a>
 </div>

 <div class="bg-card rounded-lg shadow">
 <div class="p-4 border-b">
 <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search products...') }}"
 class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 </div>

 <div class="overflow-x-auto">
 <table class="w-full">
 <thead>
 <tr class="border-b bg-page">
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain cursor-pointer"
 wire:click="sortBy('name')">
 {{ __('Name') }}
 @if($sortBy === 'name')
 <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
 @endif
 </th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Description') }}</th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Price') }}</th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Unit') }}</th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Tax') }}</th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Inventory') }}</th>
 <th class="text-right py-3 px-4 text-sm font-semibold text-txmain">{{ __('Actions') }}</th>
 </tr>
 </thead>
 <tbody>
 @forelse($products as $product)
 <tr class="border-b hover:bg-page">
 <td class="py-3 px-4 font-medium text-txmain">{{ $product->name }}</td>
 <td class="py-3 px-4 text-txmain">{{ Str::limit($product->description, 50) ?? '-' }}</td>
 <td class="py-3 px-4 text-txmain">
 {{ $profile->currency_symbol }}{{ number_format($product->price, 2) }}
 </td>
 <td class="py-3 px-4 text-txmain">{{ $product->unit }}</td>
 <td class="py-3 px-4 text-txmain">{{ $product->tax_rate }}%</td>
 <td class="py-3 px-4">
 @if($product->manage_stock)
 <span
 class="px-2 py-1 rounded text-xs font-semibold {{ $product->stock_quantity > 5 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
 {{ $product->stock_quantity }} {{ __('in stock') }}
 </span>
 @else
 <span class="text-gray-400">-</span>
 @endif
 </td>
 <td class="py-3 px-4 text-right">
 <div class="flex justify-end gap-2">
 <a href="{{ route('invoicemaker.products.edit', $product) }}"
 class="text-brand-600 hover:text-brand-700 text-sm font-medium">{{ __('Edit') }}</a>
 <button wire:click="delete({{ $product->id }})"
 wire:confirm="{{ __('Are you sure you want to delete this product?') }}"
 class="text-red-600 hover:text-red-700 text-sm font-medium">{{ __('Delete') }}</button>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="py-8 text-center text-gray-500">
 {{ __('No products found.') }} <a href="{{ route('invoicemaker.products.create') }}"
 class="text-brand-600 hover:text-brand-700">{{ __('Add your first product') }}</a>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if($products->hasPages())
 <div class="p-4 border-t flex justify-between items-center">
 <span class="text-sm text-txmain">
 {{ __('Showing') }} {{ $products->firstItem() }} {{ __('to') }} {{ $products->lastItem() }}
 {{ __('of') }} {{ $products->total() }} {{ __('results') }}
 </span>
 {{ $products->links() }}
 </div>
 @endif
 </div>
</div>