@php $title = __('Profitability & Cost Analysis'); @endphp

<div class="space-y-8">
    {{-- Header & Top Actions --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 border-b border-gray-100 pb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                    {{ __('Financial Intelligence') }}
                </span>
                <span class="text-xs text-gray-400 font-medium">
                    {{ $periodDays }} {{ __('days') }} · {{ $periodMonths }} {{ __('months') }}
                </span>
            </div>
            <h2 class="text-3xl font-bold text-txmain tracking-tight">{{ __('Profitability & Cost Analysis') }}</h2>
            <p class="text-gray-500 text-sm">
                {{ __('Track fixed and variable cost benchmarks, absolute minimum revenue, and break-even KPIs.') }}
            </p>
        </div>

        <div class="flex flex-col md:flex-row items-stretch md:items-center gap-4">
            {{-- Unified Search --}}
            <div class="relative w-full md:w-64 group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400 group-focus-within:text-brand-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="{{ __('Search client or product...') }}"
                       class="w-full pl-10 pr-3 py-2 bg-card border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm font-medium transition-all outline-none">
            </div>

            {{-- Export Button --}}
            <a href="{{ route('invoicemaker.reports.profitability.export', ['startDate' => $startDate, 'endDate' => $endDate]) }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition-all text-sm shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                {{ __('Export Excel') }}
            </a>
        </div>
    </div>

    {{-- TIME PERIOD SELECTOR BAR --}}
    <div class="bg-card rounded-2xl border border-gray-100 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center flex-wrap gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400 mr-2">{{ __('Time Period:') }}</span>
            <button type="button" wire:click="setPeriod('1M')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $period === '1M' ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ __('Past Month (1M)') }}
            </button>
            <button type="button" wire:click="setPeriod('3M')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $period === '3M' ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ __('Past 3 Months (Quarter)') }}
            </button>
            <button type="button" wire:click="setPeriod('6M')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $period === '6M' ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ __('Last Half-Year (6M)') }}
            </button>
            <button type="button" wire:click="setPeriod('12M')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $period === '12M' ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ __('Last Full Year (12M)') }}
            </button>
        </div>

        {{-- Custom Date Range Pickers --}}
        <div class="flex items-center gap-2 bg-page px-3 py-1.5 rounded-xl border border-gray-200 shrink-0">
            <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase">{{ __('From:') }}</span>
                <input type="date" wire:model.live="startDate" class="border-none bg-transparent focus:ring-0 text-xs font-bold text-txmain p-0 cursor-pointer">
            </div>
            <span class="text-gray-300 font-bold">→</span>
            <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase">{{ __('To:') }}</span>
                <input type="date" wire:model.live="endDate" class="border-none bg-transparent focus:ring-0 text-xs font-bold text-txmain p-0 cursor-pointer">
            </div>
        </div>
    </div>

    {{-- 1. COST BENCHMARKS & ABSOLUTE MINIMUM REVENUE (BREAK-EVEN) CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Fixed Costs --}}
        <div class="bg-card rounded-2xl border border-amber-200/80 p-5 shadow-xs flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-full -mr-8 -mt-8 pointer-events-none opacity-60"></div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800">
                        🔒 {{ __('Fixed Costs') }}
                    </span>
                    <span class="text-[11px] font-mono text-gray-400 font-semibold">{{ $periodMonths }}M Total</span>
                </div>
                <p class="text-2xl font-black text-slate-900 tracking-tight mt-1">
                    {{ number_format($totalFixedCosts, 2, '.', ',') }} €
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 font-medium">{{ __('Monthly Average:') }}</span>
                    <strong class="text-sm font-bold text-amber-700 font-mono">~{{ number_format($monthlyAvgFixed, 2, '.', ',') }} €/mo</strong>
                </div>
                <p class="text-[11px] text-gray-400 mt-1 leading-tight">{{ __('Rent, SaaS, salaries, and invariant obligations.') }}</p>
            </div>
        </div>

        {{-- Variable Costs --}}
        <div class="bg-card rounded-2xl border border-blue-200/80 p-5 shadow-xs flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -mr-8 -mt-8 pointer-events-none opacity-60"></div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800">
                        📊 {{ __('Variable Costs') }}
                    </span>
                    <span class="text-[11px] font-mono text-gray-400 font-semibold">{{ $periodMonths }}M Total</span>
                </div>
                <p class="text-2xl font-black text-slate-900 tracking-tight mt-1">
                    {{ number_format($totalVariableCosts, 2, '.', ',') }} €
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 font-medium">{{ __('Monthly Average:') }}</span>
                    <strong class="text-sm font-bold text-blue-700 font-mono">~{{ number_format($monthlyAvgVariable, 2, '.', ',') }} €/mo</strong>
                </div>
                <p class="text-[11px] text-gray-400 mt-1 leading-tight">{{ __('Material, travel, supplier fees, & job expenses.') }}</p>
            </div>
        </div>

        {{-- Absolute Minimum Revenue Benchmark --}}
        <div class="bg-card rounded-2xl border border-indigo-200/80 p-5 shadow-xs flex flex-col justify-between relative overflow-hidden bg-gradient-to-br from-white to-indigo-50/30">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-8 -mt-8 pointer-events-none opacity-60"></div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800">
                        🎯 {{ __('Minimum Revenue Baseline') }}
                    </span>
                </div>
                <p class="text-2xl font-black text-indigo-900 tracking-tight mt-1">
                    {{ number_format($absoluteMinMonthlyRevenue, 2, '.', ',') }} € <span class="text-xs font-semibold text-gray-500 font-sans">/ {{ __('mo') }}</span>
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 font-medium">{{ __('Period Break-Even Target:') }}</span>
                    <strong class="text-sm font-bold text-slate-800 font-mono">{{ number_format($totalExpenses, 2, '.', ',') }} €</strong>
                </div>
                <p class="text-[11px] text-indigo-600/80 font-medium mt-1 leading-tight">{{ __('Absolute revenue threshold to cover 100% of costs without deficit.') }}</p>
            </div>
        </div>

        {{-- Real-Time Break-Even Trend & Gap Analysis KPI --}}
        <div class="bg-card rounded-2xl border {{ $isBreakEvenMet ? 'border-emerald-300 bg-emerald-50/20' : 'border-rose-300 bg-rose-50/20' }} p-5 shadow-xs flex flex-col justify-between relative overflow-hidden">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $isBreakEvenMet ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $isBreakEvenMet ? '🟢 ' . __('Profit Surplus') : '🔴 ' . __('Deficit / Gap') }}
                    </span>
                    <span class="text-xs font-bold font-mono {{ $isBreakEvenMet ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $costCoveragePercent }}% {{ __('Covered') }}
                    </span>
                </div>
                <p class="text-2xl font-black {{ $isBreakEvenMet ? 'text-emerald-700' : 'text-rose-700' }} tracking-tight mt-1">
                    {{ ($revenueGap >= 0 ? '+' : '') . number_format($revenueGap, 2, '.', ',') }} €
                </p>
            </div>

            {{-- Progress Bar --}}
            <div class="mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-500 {{ $isBreakEvenMet ? 'bg-emerald-500' : ($costCoveragePercent >= 70 ? 'bg-amber-500' : 'bg-rose-500') }}"
                         style="width: {{ min(100, $costCoveragePercent) }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px] text-gray-500">
                    <span>{{ __('Actual Revenue:') }} <strong class="text-slate-800 font-mono">{{ number_format($totalRevenue, 2) }} €</strong></span>
                    @if(!$isBreakEvenMet)
                        <span class="text-rose-600 font-semibold">{{ number_format(abs($revenueGap), 2) }} € {{ __('needed') }}</span>
                    @endif
                </div>
                <p class="text-[10px] text-gray-400 mt-1">
                    📈 {{ __('Run-Rate Projection:') }} <strong class="text-slate-700 font-mono">{{ number_format($projectedRevenue, 2) }} €</strong> ({{ $projectedCoverage }}% {{ __('target') }})
                </p>
            </div>
        </div>
    </div>

    {{-- 2. COST CATEGORIES ACCORDION / BREAKDOWN --}}
    @if(count($costCategories) > 0)
        <div class="bg-card rounded-2xl border border-gray-100 p-6 shadow-xs" x-data="{ open: true }">
            <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                        📊
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-txmain uppercase tracking-wider">{{ __('Cost Categories Breakdown (Fixed vs Variable)') }}</h3>
                        <p class="text-xs text-gray-400">{{ __('Detailed view of expense groups contributing to your minimum revenue baseline') }}</p>
                    </div>
                </div>
                <button type="button" class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                    <span x-text="open ? '{{ __('Collapse') }}' : '{{ __('Expand') }}'"></span>
                    <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <div x-show="open" x-cloak class="mt-5 pt-4 border-t border-gray-100 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-page/50 border-b border-gray-100">
                            <th class="px-4 py-2.5 text-[10px] font-bold text-gray-500 uppercase">{{ __('Category Name') }}</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold text-gray-500 uppercase">{{ __('Type') }}</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold text-gray-500 uppercase">{{ __('Transactions') }}</th>
                            <th class="px-4 py-2.5 text-right text-[10px] font-bold text-gray-500 uppercase">{{ __('Total Amount') }}</th>
                            <th class="px-4 py-2.5 text-right text-[10px] font-bold text-gray-500 uppercase">{{ __('Monthly Avg') }}</th>
                            <th class="px-4 py-2.5 text-right text-[10px] font-bold text-gray-500 uppercase">{{ __('Share of Costs') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($costCategories as $cat)
                            <tr class="hover:bg-page/50 transition-colors">
                                <td class="px-4 py-3 text-sm font-semibold text-txmain">
                                    {{ $cat['name'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($cat['is_fixed'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            🔒 {{ __('Fixed') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                            📊 {{ __('Variable') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-xs font-mono text-gray-500">
                                    {{ $cat['count'] }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-sm font-bold text-slate-900">
                                    {{ number_format($cat['total'], 2, '.', ',') }} €
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-gray-600">
                                    ~{{ number_format($cat['total'] / max(0.5, $periodMonths), 2, '.', ',') }} €/mo
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs font-bold text-slate-700">
                                    {{ $totalExpenses > 0 ? number_format(($cat['total'] / $totalExpenses) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- 3. TOP PROFIT DRIVERS (CLIENTS & PRODUCTS) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Top Clients --}}
        <div class="bg-brand-50/50 rounded-2xl p-5 border border-brand-100/50">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-[10px] font-bold text-brand-600 uppercase tracking-widest">
                    {{ __('Top Profit Drivers: Clients') }}
                </h4>
            </div>
            <div class="space-y-2">
                @forelse($topClients as $client)
                    <div class="bg-card px-4 py-2.5 rounded-xl flex items-center justify-between shadow-xs border border-brand-50/50">
                        <div class="flex items-center gap-3">
                            <span class="w-5 h-5 rounded bg-brand-600 text-white text-[9px] flex items-center justify-center font-bold">{{ $loop->iteration }}</span>
                            <span class="text-sm font-semibold text-txmain">{{ $client['name'] }}</span>
                        </div>
                        <span class="text-sm font-bold text-brand-600">+{{ number_format($client['difference'], 2, '.', ',') }} €</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 font-medium italic">{{ __('No data available for the selected range.') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Top Products --}}
        <div class="bg-brand-50/50 rounded-2xl p-5 border border-brand-100/50">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-[10px] font-bold text-brand-600 uppercase tracking-widest">
                    {{ __('Top Profit Drivers: Products') }}
                </h4>
            </div>
            <div class="space-y-2">
                @forelse($topProducts as $product)
                    <div class="bg-card px-4 py-2.5 rounded-xl flex items-center justify-between shadow-xs border border-brand-50/50">
                        <div class="flex items-center gap-3">
                            <span class="w-5 h-5 rounded bg-brand-600 text-white text-[9px] flex items-center justify-center font-bold">{{ $loop->iteration }}</span>
                            <span class="text-sm font-semibold text-txmain">{{ $product['name'] }}</span>
                        </div>
                        <span class="text-sm font-bold text-brand-600">+{{ number_format($product['difference'], 2, '.', ',') }} €</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 font-medium italic">{{ __('No data available for the selected range.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 4. CUSTOMER ROI & PRODUCT MARGIN TABLES --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Client ROI --}}
        <div class="bg-card rounded-2xl border border-gray-100 overflow-hidden shadow-xs">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-page/50">
                <h3 class="text-sm font-bold text-txmain uppercase tracking-wider">{{ __('Client Performance') }}</h3>
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Total Invoiced vs Direct Costs') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-page/50 border-b border-gray-100">
                            <th class="px-6 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Client') }}</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Sales') }}</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Costs') }}</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Performance') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($clientProfitability as $client)
                            <tr class="hover:bg-page/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-txmain group-hover:text-brand-600 transition-colors">{{ $client['name'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-txmain">{{ number_format($client['sales'], 2, '.', ',') }} €</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-red-500">{{ number_format($client['costs'], 2, '.', ',') }} €</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        @if($client['margin'] > 30)
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-700">{{ __('KEEP') }}</span>
                                        @elseif($client['margin'] > 10)
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-orange-100 text-orange-700">{{ __('OPTIMIZE') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">{{ __('REVIEW') }}</span>
                                        @endif
                                        <span class="text-[10px] font-bold text-gray-400 mt-0.5">{{ number_format($client['margin'], 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm italic">{{ __('No client profitability records in this period.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Product Margins --}}
        <div class="bg-card rounded-2xl border border-gray-100 overflow-hidden shadow-xs">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-page/50">
                <h3 class="text-sm font-bold text-txmain uppercase tracking-wider">{{ __('Product Performance') }}</h3>
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Total Invoiced vs Estimated Costs') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-page/50 border-b border-gray-100">
                            <th class="px-6 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Sales') }}</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Costs') }}</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Performance') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($productProfitability as $product)
                            <tr class="hover:bg-page/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-txmain group-hover:text-brand-600 transition-colors uppercase">{{ $product['name'] }}</span>
                                    @if($product['sold'] > 0)
                                        <div class="text-[10px] text-gray-400 font-medium uppercase mt-0.5">
                                            {{ $product['sold'] }} {{ __('sold') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-txmain">{{ number_format($product['sales'], 2, '.', ',') }} €</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-red-500">{{ number_format($product['costs'], 2, '.', ',') }} €</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        @if($product['margin'] > 30)
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-700">{{ __('KEEP') }}</span>
                                        @elseif($product['margin'] > 10)
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-orange-100 text-orange-700">{{ __('OPTIMIZE') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">{{ __('REVIEW') }}</span>
                                        @endif
                                        <span class="text-[10px] font-bold text-gray-400 mt-0.5">{{ number_format($product['margin'], 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm italic">{{ __('No product records in this period.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>