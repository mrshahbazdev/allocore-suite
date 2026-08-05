@php $title = __('Expenses'); @endphp

<div>
 <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <div>
 <h2 class="text-2xl font-bold text-txmain">{{ __('Expenses') }}</h2>
 <p class="text-txmain">{{ __('Track and manage your business expenditures') }}</p>
 </div>
 <div class="flex gap-2">
 <a href="{{ route('invoicemaker.expenses.create') }}"
 class="bg-brand-600 text-white py-2 px-4 rounded-lg hover:bg-brand-700 shadow-sm transition duration-200 text-center font-medium">
 + {{ __('Record Expense') }}
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
 <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search expenses...') }}"
 class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">

 <select wire:model.live="category"
 class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="">{{ __('All Categories') }}</option>
 @foreach($categories as $cat)
 <option value="{{ $cat }}">{{ __($cat) }}</option>
 @endforeach
 </select>
 </div>

 <div class="overflow-x-auto">
 <table class="w-full">
 <thead>
 <tr class="border-b bg-page">
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain cursor-pointer"
 wire:click="sortBy('date')">
 {{ __('Date') }}
 @if($sortBy === 'date')
 <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
 @endif
 </th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain cursor-pointer"
 wire:click="sortBy('category')">
 {{ __('Category') }}
 @if($sortBy === 'category')
 <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
 @endif
 </th>
 <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Description') }}</th>
 <th class="text-right py-3 px-4 text-sm font-semibold text-txmain cursor-pointer"
 wire:click="sortBy('amount')">
 {{ __('Amount') }}
 @if($sortBy === 'amount')
 <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
 @endif
 </th>
 <th class="text-right py-3 px-4 text-sm font-semibold text-txmain">{{ __('Actions') }}</th>
 </tr>
 </thead>
 <tbody>
 @forelse($expenses as $expense)
 <tr class="border-b hover:bg-page cursor-pointer"
 onclick="window.location='{{ route('invoicemaker.expenses.show', $expense) }}'">
 <td class="py-3 px-4 text-txmain">{{ $expense->date->format('M d, Y') }}</td>
 <td class="py-3 px-4">
 <span class="px-2 py-0.5 rounded text-xs font-medium bg-page text-txmain">
 {{ __($expense->category) }}
 </span>
 </td>
 <td class="py-3 px-4 text-txmain">{{ $expense->description }}</td>
 <td class="py-3 px-4 text-right font-semibold text-txmain">
 {{ $profile->currency_symbol }}{{ number_format($expense->amount, 2) }}
 </td>
 <td class="py-3 px-4 text-right" onclick="event.stopPropagation()">
 <div class="flex justify-end gap-3">
 <a href="{{ route('invoicemaker.expenses.show', $expense) }}"
 class="text-brand-600 hover:text-brand-700 text-sm font-medium pr-2">{{ __('View') }}</a>
 @if($expense->receipt_path)
 <a href="{{ Storage::url($expense->receipt_path) }}" target="_blank"
 class="text-brand-600 hover:text-brand-700 text-sm font-medium"
 title="{{ __('View Receipt') }}">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
 </path>
 </svg>
 </a>
 @endif
 <a href="{{ route('invoicemaker.expenses.edit', $expense) }}"
 class="text-brand-600 hover:text-brand-700 text-sm font-medium">{{ __('Edit') }}</a>
 <button wire:click="delete({{ $expense->id }})"
 wire:confirm="{{ __('Are you sure you want to delete this expense?') }}"
 class="text-red-600 hover:text-red-700 text-sm font-medium">{{ __('Delete') }}</button>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="py-8 text-center text-gray-500">
 {{ __('No expenses recorded yet.') }} <a href="{{ route('invoicemaker.expenses.create') }}"
 class="text-brand-600 hover:text-brand-700">{{ __('Record your first expense') }}</a>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if($expenses->hasPages())
 <div class="p-4 border-t">
 {{ $expenses->links() }}
 </div>
 @endif
 </div>
</div>