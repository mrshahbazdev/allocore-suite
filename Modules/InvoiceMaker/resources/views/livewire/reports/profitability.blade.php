@php $title = __('Profitability Report'); @endphp

<div class="space-y-8">
 <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 border-b border-gray-100 pb-8">
 <div>
 <h2 class="text-3xl font-bold text-txmain tracking-tight">{{ __('Profitability Report') }}</h2>
 <p class="text-gray-500 text-base">
 {{ __('Analyze which customers and products drive your growth.') }}
 </p>
 </div>

 <div class="flex flex-col md:flex-row items-center gap-6">
 <!-- Advanced Filters Label -->
 <div class="hidden xl:block">
 <span
 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Advanced Filters') }}</span>
 </div>

 <!-- Unified Search -->
 <div class="relative w-full md:w-80 group">
 <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
 <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-600 transition-colors" fill="none"
 stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
 </svg>
 </div>
 <input type="text" wire:model.live.debounce.300ms="search"
 placeholder="{{ __('Search client or product...') }}"
 class="w-full pl-11 pr-4 py-3 bg-card border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm font-medium transition-all outline-none">
 </div>

 <!-- Date Range -->
 <div
 class="flex items-center gap-3 bg-card px-4 py-2.5 rounded-xl border border-gray-200 w-full md:w-auto">
 <div class="flex flex-col">
 <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">{{ __('From') }}</span>
 <input type="date" wire:model.live="startDate"
 class="border-none focus:ring-0 text-sm font-bold text-txmain p-0">
 </div>
 <span class="text-gray-300 font-bold">→</span>
 <div class="flex flex-col">
 <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">{{ __('To') }}</span>
 <input type="date" wire:model.live="endDate"
 class="border-none focus:ring-0 text-sm font-bold text-txmain p-0">
 </div>
 </div>

 <!-- Export Button -->
 <a href="{{ route('invoicemaker.reports.profitability.export', ['startDate' => $startDate, 'endDate' => $endDate]) }}"
 class="flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition-all text-sm w-full md:w-auto justify-center shadow-sm">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
 </svg>
 {{ __('Export to Excel') }}
 </a>
 </div>
 </div>

 <!-- Summary Cards -->
 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div
 class="bg-card p-6 rounded-2xl border border-gray-100 flex items-center group transition-all duration-300 shadow-sm hover:border-brand-200">
 <div
 class="w-12 h-12 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center mr-4 group-hover:bg-brand-600 group-hover:text-white transition-all duration-300">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
 </svg>
 </div>
 <div class="absolute right-0 top-0 w-1 pt-12 h-full bg-brand-500"></div>
 <div>
 <span
 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Overall Revenue') }}</span>
 <span class="text-[8px] text-gray-400 block -mt-1">{{ __('(Invoiced + Manual)') }}</span>
 <p class="text-2xl font-bold text-txmain mt-0.5">
 {{ number_format($totalRevenue, 2, '.', ',') }} €
 </p>
 </div>
 </div>

 <div
 class="bg-card p-6 rounded-2xl border border-gray-100 flex items-center group transition-all duration-300 shadow-sm hover:border-red-200">
 <div
 class="w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center mr-4 group-hover:bg-red-600 group-hover:text-white transition-all duration-300">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
 </svg>
 </div>
 <div>
 <span
 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Total Expenses') }}</span>
 <p class="text-2xl font-bold text-red-600 mt-0.5">
 {{ number_format($totalExpenses, 2, '.', ',') }} €
 </p>
 </div>
 </div>

 <div
 class="bg-card p-6 rounded-2xl border border-gray-100 flex items-center group transition-all duration-300 shadow-sm hover:border-emerald-200 relative overflow-hidden">
 <div class="absolute right-0 top-0 w-1 pt-12 h-full bg-emerald-500"></div>
 <div
 class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mr-4 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3m0 10c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8m0-18C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z">
 </path>
 </svg>
 </div>
 <div>
 <span
 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Net Profit') }}</span>
 <p class="text-2xl font-bold text-txmain mt-0.5">
 {{ number_format($netIncome, 2, '.', ',') }} €
 </p>
 </div>
 </div>
 </div>

 <!-- Top Performers Overview -->
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <!-- Top Clients -->
 <div class="bg-brand-50/50 rounded-2xl p-5 border border-brand-100/50">
 <div class="flex items-center justify-between mb-4">
 <h4 class="text-[10px] font-bold text-brand-600 uppercase tracking-widest">
 {{ __('Top Profit Drivers: Clients') }}
 </h4>
 </div>
 <div class="space-y-2">
 @forelse($topClients as $client)
 <div
 class="bg-card px-4 py-2.5 rounded-xl flex items-center justify-between shadow-sm border border-brand-50/50">
 <div class="flex items-center gap-3">
 <span
 class="w-5 h-5 rounded bg-brand-600 text-white text-[9px] flex items-center justify-center font-bold">{{ $loop->iteration }}</span>
 <span class="text-sm font-semibold text-txmain">{{ $client['name'] }}</span>
 </div>
 <span class="text-sm font-bold text-brand-600">+
 {{ number_format($client['difference'], 2, '.', ',') }} €</span>
 </div>
 @empty
 <p class="text-xs text-gray-400 font-medium italic">
 {{ __('No data available for the selected range.') }}</p>
 @endforelse
 </div>
 </div>

 <!-- Top Products -->
 <div class="bg-brand-50/50 rounded-2xl p-5 border border-brand-100/50">
 <div class="flex items-center justify-between mb-4">
 <h4 class="text-[10px] font-bold text-brand-600 uppercase tracking-widest">
 {{ __('Top Profit Drivers: Products') }}
 </h4>
 </div>
 <div class="space-y-2">
 @forelse($topProducts as $product)
 <div
 class="bg-card px-4 py-2.5 rounded-xl flex items-center justify-between shadow-sm border border-brand-50/50">
 <div class="flex items-center gap-3">
 <span
 class="w-5 h-5 rounded bg-brand-600 text-white text-[9px] flex items-center justify-center font-bold">{{ $loop->iteration }}</span>
 <span class="text-sm font-semibold text-txmain">{{ $product['name'] }}</span>
 </div>
 <span class="text-sm font-bold text-brand-600">+
 {{ number_format($product['difference'], 2, '.', ',') }} €</span>
 </div>
 @empty
 <p class="text-xs text-gray-400 font-medium italic">
 {{ __('No data available for the selected range.') }}</p>
 @endforelse
 </div>
 </div>
 </div>

 <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
 <!-- Client ROI -->
 <div class="bg-card rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
 <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-page/50">
 <h3 class="text-sm font-bold text-txmain uppercase tracking-wider">{{ __('Client Performance') }}</h3>
 <span
 class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Total Invoiced vs Direct Costs') }}</span>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse">
 <thead>
 <tr class="bg-page/50 border-b border-gray-100">
 <th class="px-6 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
 {{ __('Client') }}</th>
 <th
 class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">
 {{ __('Sales') }}</th>
 <th
 class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">
 {{ __('Costs') }}</th>
 <th
 class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">
 {{ __('Performance') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-gray-50">
 @foreach($clientProfitability as $client)
 <tr class="hover:bg-page/50 transition-colors group">
 <td class="px-6 py-4">
 <span
 class="text-sm font-bold text-txmain group-hover:text-brand-600 transition-colors">{{ $client['name'] }}</span>
 </td>
 <td class="px-6 py-4 text-right">
 <span
 class="text-sm font-medium text-txmain">{{ number_format($client['sales'], 2, '.', ',') }}
 €</span>
 </td>
 <td class="px-6 py-4 text-right">
 <span
 class="text-sm font-medium text-red-500">{{ number_format($client['costs'], 2, '.', ',') }}
 €</span>
 </td>
 <td class="px-6 py-4 text-right">
 <div class="flex flex-col items-end">
 @if($client['margin'] > 30)
 <span
 class="px-2 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-700">{{ __('KEEP') }}</span>
 @elseif($client['margin'] > 10)
 <span
 class="px-2 py-0.5 rounded text-[9px] font-bold bg-orange-100 text-orange-700">{{ __('OPTIMIZE') }}</span>
 @else
 <span
 class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">{{ __('REVIEW') }}</span>
 @endif
 <span
 class="text-[10px] font-bold text-gray-400 mt-0.5">{{ number_format($client['margin'], 1) }}%</span>
 </div>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 </div>

 <!-- Product Margins -->
 <div class="bg-card rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
 <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-page/50">
 <h3 class="text-sm font-bold text-txmain uppercase tracking-wider">{{ __('Product Performance') }}</h3>
 <span
 class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Total Invoiced vs Estimated Costs') }}</span>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse">
 <thead>
 <tr class="bg-page/50 border-b border-gray-100">
 <th class="px-6 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
 {{ __('Product') }}</th>
 <th
 class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">
 {{ __('Sales') }}</th>
 <th
 class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">
 {{ __('Costs') }}</th>
 <th
 class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">
 {{ __('Performance') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-gray-50">
 @foreach($productProfitability as $product)
 <tr class="hover:bg-page/50 transition-colors group">
 <td class="px-6 py-4">
 <span
 class="text-sm font-bold text-txmain group-hover:text-brand-600 transition-colors uppercase">{{ $product['name'] }}</span>
 @if($product['sold'] > 0)
 <div class="text-[10px] text-gray-400 font-medium uppercase mt-0.5">
 {{ $product['sold'] }} {{ __('sold') }}</div>
 @endif
 </td>
 <td class="px-6 py-4 text-right">
 <span
 class="text-sm font-medium text-txmain">{{ number_format($product['sales'], 2, '.', ',') }}
 €</span>
 </td>
 <td class="px-6 py-4 text-right">
 <span
 class="text-sm font-medium text-red-500">{{ number_format($product['costs'], 2, '.', ',') }}
 €</span>
 </td>
 <td class="px-6 py-4 text-right">
 <div class="flex flex-col items-end">
 @if($product['margin'] > 30)
 <span
 class="px-2 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-700">{{ __('KEEP') }}</span>
 @elseif($product['margin'] > 10)
 <span
 class="px-2 py-0.5 rounded text-[9px] font-bold bg-orange-100 text-orange-700">{{ __('OPTIMIZE') }}</span>
 @else
 <span
 class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">{{ __('REVIEW') }}</span>
 @endif
 <span
 class="text-[10px] font-bold text-gray-400 mt-0.5">{{ number_format($product['margin'], 1) }}%</span>
 </div>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 </div>
 </div>
</div>
</div>
</div>
</div>