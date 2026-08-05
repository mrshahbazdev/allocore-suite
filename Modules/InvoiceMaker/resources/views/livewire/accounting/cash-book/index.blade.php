@php $title = __('Cash Book'); @endphp

<div>
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-8 flex flex-col xl:flex-row items-stretch justify-between gap-6">
        <div class="flex-1">
            <h2 class="text-3xl font-extrabold text-txmain tracking-tight">{{ __('Cash Book') }}</h2>
            <p class="text-gray-500 font-medium">{{ __('Monitor your flow of capital with precision.') }}</p>
        </div>

        <div
            class="xl:w-2/3 grid grid-cols-1 sm:grid-cols-3 gap-0 bg-card rounded-2xl shadow-xl shadow-brand-900/5 border border-gray-100 overflow-hidden">
            <!-- Income Card -->
            <div
                class="px-6 py-5 border-b sm:border-b-0 sm:border-r border-gray-100 flex items-center group hover:bg-green-50/30 transition-colors">
                <div
                    class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <span
                        class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">{{ __('Total Income') }}</span>
                    <p class="text-xl font-black text-green-700 leading-none mt-1">
                        {{ number_format($incomeTotal, 2, ',', '.') }} €
                    </p>
                </div>
            </div>

            <!-- Expense Card -->
            <div
                class="px-6 py-5 border-b sm:border-b-0 sm:border-r border-gray-100 flex items-center group hover:bg-red-50/30 transition-colors">
                <div
                    class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </div>
                <div>
                    <span
                        class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">{{ __('Total Expenses') }}</span>
                    <p class="text-xl font-black text-red-700 leading-none mt-1">
                        {{ number_format($expenseTotal, 2, ',', '.') }} €
                    </p>
                </div>
            </div>

            <!-- Balance Card -->
            <div class="px-6 py-5 flex items-center group bg-brand-600 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <svg class="w-16 h-16" fill="white" viewBox="0 0 24 24">
                        <path
                            d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3m0 10c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8m0-18C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z">
                        </path>
                    </svg>
                </div>
                <div
                    class="w-12 h-12 bg-card/20 text-white rounded-xl flex items-center justify-center mr-4 backdrop-blur-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="relative z-10">
                    <span
                        class="text-[10px] font-black text-brand-100 uppercase tracking-[0.2em]">{{ __('Net Balance') }}</span>
                    <p class="text-2xl font-black text-white leading-none mt-1">
                        {{ number_format($balance, 2, ',', '.') }} €
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <button wire:click="generateInsights" wire:loading.attr="disabled"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-brand-600 border border-transparent rounded-xl shadow-lg shadow-brand-600/30 text-sm font-bold text-white hover:bg-brand-700 transition-all group disabled:opacity-50">
                <svg wire:loading wire:target="generateInsights" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span wire:loading.remove wire:target="generateInsights" class="mr-2">✨</span>
                {{ __('AI Insights') }}
            </button>
            <a href="{{ route('invoicemaker.cash-book.export.excel', ['startDate' => $startDate, 'endDate' => $endDate]) }}"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-card border border-gray-200 rounded-xl shadow-lg shadow-gray-200/50 text-sm font-bold text-txmain hover:bg-green-600 hover:text-white hover:border-green-600 transition-all group">
                <svg class="w-5 h-5 mr-2 text-green-600 group-hover:text-white" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                {{ __('Excel') }}
            </a>
            <a href="{{ route('invoicemaker.cash-book.export.pdf', ['startDate' => $startDate, 'endDate' => $endDate]) }}"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-card border border-gray-200 rounded-xl shadow-lg shadow-gray-200/50 text-sm font-bold text-txmain hover:bg-red-600 hover:text-white hover:border-red-600 transition-all group">
                <svg class="w-5 h-5 mr-2 text-red-600 group-hover:text-white" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                    </path>
                </svg>
                {{ __('PDF') }}
            </a>
            </a>
        </div>
    </div>

    <div class="mb-4 flex justify-end">
        <a href="{{ route('invoicemaker.accounting.reconciliation') }}"
            class="inline-flex items-center justify-center px-6 py-2 bg-blue-600 border border-transparent rounded-xl shadow-lg shadow-blue-600/30 text-sm font-bold text-white hover:bg-blue-700 transition-all group">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
            {{ __('Bank Reconciliation (CSV)') }}
        </a>
    </div>

    @if($aiInsights)

        <div class="mb-8 p-6 bg-brand-600 border border-brand-100 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-bold text-brand-900 flex items-center">
                        <span class="mr-2">✨</span> {{ __('AI Cash Flow Insights') }}
                    </h3>
                    <button wire:click="$set('aiInsights', null)" class="text-brand-400 hover:text-brand-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="prose prose-indigo max-w-none text-txmain text-sm leading-relaxed">
                    {!! nl2br(e($aiInsights)) !!}
                </div>
            </div>
        </div>
    @endif

    <div class="bg-card rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Filters -->
        <div class="p-6 bg-page/50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ __('Search') }}</label>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Partner, Booking #...') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ __('Type') }}</label>
                    <select wire:model.live="type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 text-sm">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="income">{{ __('Income') }}</option>
                        <option value="expense">{{ __('Expense') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ __('Source') }}</label>
                    <select wire:model.live="source"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 text-sm">
                        <option value="">{{ __('All Sources') }}</option>
                        <option value="bank">{{ __('Bank') }}</option>
                        <option value="cash">{{ __('Cash (Kasse)') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ __('From') }}</label>
                    <input type="date" wire:model.live="startDate"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ __('To') }}</label>
                    <input type="date" wire:model.live="endDate"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 text-sm">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-[13px] border-collapse">
                <thead>
                    <tr class="bg-page border-b border-gray-200">
                        <th class="px-3 py-3 w-8"></th>
                        <th class="px-3 py-3 text-left font-bold text-gray-500 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('booking_number')">
                            {{ __('Booking #') }}
                            @if($sortBy === 'booking_number') <span
                            class="text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">
                            {{ __('Reference') }}
                        </th>
                        <th class="px-3 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">
                            {{ __('Doc Date') }}
                        </th>
                        <th class="px-3 py-3 text-left font-bold text-gray-500 uppercase tracking-wider text-center">
                            {{ __('Status') }}
                        </th>
                        <th class="px-3 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">
                            {{ __('Partner / Company') }}
                        </th>
                        <th class="px-3 py-3 text-left font-bold text-gray-500 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('date')">
                            {{ __('Payment Date') }}
                            @if($sortBy === 'date') <span
                            class="text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-3 text-right font-bold text-gray-500 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('amount')">
                            {{ __('Amount') }}
                            @if($sortBy === 'amount') <span
                            class="text-[10px]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">
                            {{ __('Category') }}
                        </th>
                        <th class="px-3 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">
                            {{ __('Entry Date') }}
                        </th>
                        <th class="px-3 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($entries as $entry)
                        @php
                            $routeUrl = '#';
                            if ($entry->type === 'income' && $entry->invoice_id) {
                                $routeUrl = route('invoicemaker.invoices.show', $entry->invoice_id);
                            } elseif ($entry->type === 'expense' && $entry->expense_id) {
                                $routeUrl = route('invoicemaker.expenses.show', $entry->expense_id);
                            }
                         @endphp
                        <tr class="hover:bg-page cursor-pointer transition-colors {{ $loop->odd ? 'bg-card' : 'bg-page/20' }}"
                            onclick="if('{{ $routeUrl }}' !== '#') window.location='{{ $routeUrl }}'">
                            <td class="px-3 py-2 text-center">
                                <span
                                    class="inline-block w-2.5 h-2.5 rounded-full {{ $entry->type === 'income' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-500">
                                {{ $entry->booking_number }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-txmain font-semibold italic">
                                {{ $entry->reference_number ?? '-' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-txmain">
                                {{ $entry->document_date ? $entry->document_date->format('d.m.Y') : ($entry->invoice ? $entry->invoice->invoice_date->format('d.m.Y') : $entry->date->format('d.m.Y')) }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-center text-[11px] text-gray-500">
                                {{ $entry->type === 'income' ? __('vollständig bezahlt') : __('Bezahlt') }}
                            </td>
                            <td class="px-3 py-2 text-txmain font-bold max-w-xs truncate">
                                {{ $entry->partner_name ?? ($entry->invoice && $entry->invoice->client ? ($entry->invoice->client->company_name ?? $entry->invoice->client->name) : $entry->description) }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-txmain font-medium">
                                {{ $entry->date->format('d.m.Y') }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-extrabold text-txmain">
                                {{ number_format($entry->amount, 2, ',', '.') }} €
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-txmain">
                                <span
                                    class="px-2 py-0.5 rounded bg-page border border-gray-200 text-[10px] font-bold text-txmain uppercase tracking-tighter">
                                    {{ $entry->category->name ?? ($entry->type === 'income' ? __('Einnahmen') : __('Allgemeines')) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-400 text-[11px]">
                                {{ $entry->created_at->format('d.m.y') }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-right" onclick="event.stopPropagation()">
                                <button wire:click="delete({{ $entry->id }})"
                                    wire:confirm="{{ __('Are you sure you want to delete this entry?') }}"
                                    class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition"
                                    title="{{ __('Delete') }}">
                                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <p>{{ __('No entries found in this period.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
            <div class="px-6 py-4 bg-page border-t border-gray-200">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>