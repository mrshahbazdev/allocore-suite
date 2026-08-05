@php $title = __('Dashboard'); @endphp

<div>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-txmain">{{ __('Dashboard') }}</h2>
        <p class="text-txmain">{{ __('Overview of your invoice business') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-invoicemaker::stats-card title="{{ __('Total Invoices') }}" :value="$stats['total_invoices']" color="blue">
            <x-slot:icon>
                <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </x-slot:icon>
        </x-invoicemaker::stats-card>

        <x-invoicemaker::stats-card title="{{ __('Total Revenue') }}" :value="$profile->currency_symbol . number_format($stats['total_revenue'], 2)" color="green">
            <x-slot:icon>
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </x-slot:icon>
        </x-invoicemaker::stats-card>

        <x-invoicemaker::stats-card title="{{ __('Total Clients') }}" :value="$stats['total_clients']" color="purple">
            <x-slot:icon>
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
            </x-slot:icon>
        </x-invoicemaker::stats-card>

        <x-invoicemaker::stats-card title="{{ __('Total Expenses') }}" :value="$profile->currency_symbol . number_format($stats['total_expenses'], 2)" color="red">
            <x-slot:icon>
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </x-slot:icon>
        </x-invoicemaker::stats-card>

        <x-invoicemaker::stats-card title="{{ __('Net Profit') }}" :value="$profile->currency_symbol . number_format($stats['net_profit'], 2)" color="{{ $stats['net_profit'] >= 0 ? 'indigo' : 'red' }}">
            <x-slot:icon>
                <svg class="w-6 h-6 text-{{ $stats['net_profit'] >= 0 ? 'indigo' : 'red' }}-600" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
            </x-slot:icon>
        </x-invoicemaker::stats-card>

        <x-invoicemaker::stats-card title="{{ __('Pending Amount') }}" :value="$profile->currency_symbol . number_format($stats['pending_amount'], 2)" color="yellow">
            <x-slot:icon>
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </x-slot:icon>
        </x-invoicemaker::stats-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-card rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Recent Invoices') }}</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 text-sm font-medium text-txmain">{{ __('Invoice') }}</th>
                            <th class="text-left py-2 text-sm font-medium text-txmain">{{ __('Client') }}</th>
                            <th class="text-left py-2 text-sm font-medium text-txmain">{{ __('Amount') }}</th>
                            <th class="text-left py-2 text-sm font-medium text-txmain">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentInvoices as $invoice)
                            <tr class="border-b">
                                <td class="py-3 text-sm">
                                    <a href="{{ route('invoicemaker.invoices.show', $invoice) }}"
                                        class="text-brand-600 hover:text-brand-700 font-medium">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="py-3 text-sm text-txmain">{{ $invoice->client->name }}</td>
                                <td class="py-3 text-sm text-txmain">
                                    {{ $profile->currency_symbol }}{{ number_format($invoice->grand_total, 2) }}
                                </td>
                                <td class="py-3">
                                    @if($invoice->status === 'sent')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-unpaid-bg text-unpaid-text">
                                            {{ __('Unpaid') }}
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $invoice->status_color }}-100 text-{{ $invoice->status_color }}-700">
                                            {{ __(ucfirst($invoice->status)) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">{{ __('No invoices yet') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentInvoices->count() > 0)
                <div class="mt-4">
                    <a href="{{ route('invoicemaker.invoices.index') }}"
                        class="text-brand-600 hover:text-brand-700 text-sm font-medium">{{ __('View all invoices') }} →</a>
                </div>
            @endif
        </div>

        <div class="bg-card rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Revenue vs Expenses') }} ({{ now()->year }})
            </h3>
            <div class="h-64 flex items-stretch justify-between gap-2 border-b border-gray-100 pb-2">
                @for($month = 1; $month <= 12; $month++)
                    <div class="flex-1 flex items-end justify-center gap-1 group relative">
                        <!-- Revenue Bar -->
                        <div class="w-2.5 bg-brand-500 rounded-t"
                            style="height: {{ ($revenueByMonth[$month] ?? 0) ? min(100, (($revenueByMonth[$month] ?? 0) / $maxAmount) * 100) : 0 }}%;">
                        </div>
                        <!-- Expense Bar -->
                        <div class="w-2.5 bg-red-400 rounded-t"
                            style="height: {{ ($expensesByMonth[$month] ?? 0) ? min(100, (($expensesByMonth[$month] ?? 0) / $maxAmount) * 100) : 0 }}%;">
                        </div>

                        <div class="absolute bottom-full mb-2 hidden group-hover:block z-20">
                            <div class="bg-gray-800 text-white p-2 rounded text-[10px] shadow-xl whitespace-nowrap">
                                <div class="flex justify-between gap-4"><span>{{ __('Rev') }}:</span>
                                    <span>{{ $profile->currency_symbol }}{{ number_format($revenueByMonth[$month] ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between gap-4 text-red-300"><span>{{ __('Exp') }}:</span>
                                    <span>{{ $profile->currency_symbol }}{{ number_format($expensesByMonth[$month] ?? 0, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
            <div class="flex justify-between mt-2">
                @for($month = 1; $month <= 12; $month++)
                    <span
                        class="flex-1 text-center text-[10px] font-medium text-gray-400">{{ date('M', mktime(0, 0, 0, $month, 1)) }}</span>
                @endfor
            </div>
            <div class="mt-4 flex gap-4 text-xs font-medium justify-center">
                <div class="flex items-center"><span class="w-3 h-3 bg-brand-500 rounded-full mr-1.5"></span>
                    {{ __('Revenue') }}
                </div>
                <div class="flex items-center"><span class="w-3 h-3 bg-red-400 rounded-full mr-1.5"></span>
                    {{ __('Expenses') }}
                </div>
            </div>
        </div>
    </div>

    @if(count($expensesByCategory) > 0)
        <div class="mt-8 bg-card rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-txmain mb-6">{{ __('Expense Breakdown') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($expensesByCategory as $cat => $total)
                    <div class="p-4 border rounded-lg bg-page flex flex-col justify-between">
                        <span class="text-sm font-medium text-gray-500 uppercase">{{ $cat }}</span>
                        <span class="text-xl font-bold text-txmain mt-1">
                            {{ $profile->currency_symbol }}{{ number_format($total, 2) }}
                        </span>
                        @php
                            $percentage = ($stats['total_expenses'] > 0) ? ($total / $stats['total_expenses']) * 100 : 0;
                         @endphp
                        <div class="mt-3 w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-brand-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>