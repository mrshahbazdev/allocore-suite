@php $title = __('Estimates'); @endphp

<div>
 <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <div>
 <h2 class="text-2xl font-bold text-txmain">{{ __('Estimates & Quotations') }}</h2>
 <p class="text-txmain">{{ __('Manage your estimates and bids') }}</p>
 </div>
 <div class="flex gap-2">
 <a href="{{ route('invoicemaker.estimates.create') }}"
 class="bg-brand-600 text-white py-2 px-4 rounded-lg hover:bg-brand-700 shadow-sm transition duration-200 text-center font-medium">
 + {{ __('Create Estimate') }}
 </a>
 </div>
 </div>

 @if (session()->has('message'))
 <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
 {{ session('message') }}
 </div>
 @endif

 <div class="bg-card rounded-lg shadow">
 <div class="p-4 border-b flex flex-col md:flex-row gap-4">
 <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search estimates...') }}"
 class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <select wire:model.live="status"
 class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="">{{ __('All Statuses') }}</option>
 <option value="draft">{{ __('Draft') }}</option>
 <option value="sent">{{ __('Sent') }}</option>
 <option value="cancelled">{{ __('Cancelled') }}</option>
 </select>
 </div>

 <div class="overflow-x-auto">
 <table class="w-full">
 <thead>
 <tr class="border-b bg-page">
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain cursor-pointer"
 wire:click="sortBy('invoice_number')">
 {{ __('Estimate Number') }}
 @if($sortBy === 'invoice_number')
 <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
 @endif
 </th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Client') }}</th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain cursor-pointer"
 wire:click="sortBy('invoice_date')">
 {{ __('Date') }}
 @if($sortBy === 'invoice_date')
 <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
 @endif
 </th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Expires') }}</th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Amount') }}</th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Status') }}</th>
 <th class="text-right py-3 px-4 text-sm font-semibold text-txmain">{{ __('Actions') }}</th>
 </tr>
 </thead>
 <tbody>
 @forelse($estimates as $estimate)
 <tr class="border-b hover:bg-page">
 <td class="py-3 px-4 text-brand-600 font-medium">
 {{ $estimate->invoice_number }}
 </td>
 <td class="py-3 px-4 text-txmain">{{ $estimate->client->name }}</td>
 <td class="py-3 px-4 text-txmain">{{ $estimate->invoice_date->format('M d, Y') }}</td>
 <td class="py-3 px-4 text-txmain">{{ $estimate->due_date->format('M d, Y') }}</td>
 <td class="py-3 px-4 text-txmain font-medium">
 {{ $estimate->currency_symbol }}{{ number_format($estimate->grand_total, 2) }}
 </td>
 <td class="py-3 px-4">
 <span
 class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $estimate->status_color }}-100 text-{{ $estimate->status_color }}-700">
 {{ __(ucfirst($estimate->status)) }}
 </span>
 </td>
 <td class="py-3 px-4 text-right">
 <div class="flex justify-end gap-2">
 <a href="{{ route('invoicemaker.estimates.show', $estimate) }}"
 class="text-brand-600 hover:text-brand-700 text-sm font-medium">{{ __('View') }}</a>
 <a href="{{ route('invoicemaker.estimates.edit', $estimate) }}"
 class="text-amber-600 hover:text-amber-700 text-sm font-medium">{{ __('Edit') }}</a>
 <button wire:click="convertToInvoice({{ $estimate->id }})"
 wire:confirm="{{ __('Convert this estimate to a standard invoice?') }}"
 class="text-green-600 hover:text-green-700 text-sm font-medium">{{ __('Convert') }}</button>
 <button wire:click="delete({{ $estimate->id }})"
 wire:confirm="{{ __('Are you sure you want to delete this estimate?') }}"
 class="text-red-600 hover:text-red-700 text-sm font-medium">{{ __('Delete') }}</button>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="py-8 text-center text-gray-500">
 {{ __('No estimates found.') }} <a href="{{ route('invoicemaker.estimates.create') }}"
 class="text-brand-600 hover:text-brand-700">{{ __('Create your first estimate') }}</a>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if($estimates->hasPages())
 <div class="p-4 border-t flex justify-between items-center">
 <span class="text-sm text-txmain">
 {{ __('Showing') }} {{ $estimates->firstItem() }} {{ __('to') }} {{ $estimates->lastItem() }}
 {{ __('of') }} {{ $estimates->total() }}
 {{ __('results') }}
 </span>
 {{ $estimates->links() }}
 </div>
 @endif
 </div>
</div>