<div class="space-y-6">
 <!-- Stats Grid -->
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
 <!-- Total Invoiced -->
 <div class="bg-card rounded-xl shadow-sm p-6 border border-gray-100">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500">{{ __('Total Invoiced') }}</p>
 <p class="text-2xl font-bold text-txmain">
 {{ $profile->currency_symbol }}{{ number_format($totalInvoiced, 2) }}</p>
 </div>
 <div class="p-3 bg-brand-50 rounded-lg">
 <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
 </path>
 </svg>
 </div>
 </div>
 <div class="mt-4 flex items-center text-sm">
 <span class="text-gray-500">{{ $totalInvoices }} {{ __('invoices') }}</span>
 </div>
 </div>

 <!-- Total Paid -->
 <div class="bg-card rounded-xl shadow-sm p-6 border border-gray-100">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500">{{ __('Total Paid') }}</p>
 <p class="text-2xl font-bold text-green-600">
 {{ $profile->currency_symbol }}{{ number_format($totalPaid, 2) }}</p>
 </div>
 <div class="p-3 bg-green-50 rounded-lg">
 <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
 </path>
 </svg>
 </div>
 </div>
 <div class="mt-4 flex items-center text-sm">
 <span class="text-green-600 font-medium">{{ $paidInvoices }} {{ __('paid') }}</span>
 </div>
 </div>

 <!-- Outstanding -->
 <div class="bg-card rounded-xl shadow-sm p-6 border border-gray-100">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500">{{ __('Outstanding') }}</p>
 <p class="text-2xl font-bold text-orange-600">
 {{ $profile->currency_symbol }}{{ number_format($totalOutstanding, 2) }}</p>
 </div>
 <div class="p-3 bg-orange-50 rounded-lg">
 <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
 </svg>
 </div>
 </div>
 <div class="mt-4 flex items-center text-sm">
 <span class="text-orange-600 font-medium">{{ $overdueInvoices }} {{ __('overdue') }}</span>
 </div>
 </div>

 <!-- Total Clients -->
 <div class="bg-card rounded-xl shadow-sm p-6 border border-gray-100">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500">{{ __('Total Clients') }}</p>
 <p class="text-2xl font-bold text-purple-600">{{ $totalClients }}</p>
 </div>
 <div class="p-3 bg-purple-50 rounded-lg">
 <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
 </path>
 </svg>
 </div>
 </div>
 <div class="mt-4 flex items-center text-sm">
 <a href="{{ route('invoicemaker.clients.index') }}"
 class="text-purple-600 hover:text-purple-700">{{ __('View all clients') }} &rarr;</a>
 </div>
 </div>
 </div>

 <!-- Recent Activity -->
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
 <!-- Recent Invoices -->
 <div class="bg-card rounded-xl shadow-sm border border-gray-100">
 <div class="p-6 border-b border-gray-100">
 <h3 class="text-lg font-semibold text-txmain">{{ __('Recent Invoices') }}</h3>
 </div>
 <div class="divide-y divide-gray-100">
 @forelse($recentInvoices as $invoice)
 <div class="p-4 flex items-center justify-between hover:bg-page">
 <div>
 <p class="font-medium text-txmain">{{ $invoice->invoice_number }}</p>
 <p class="text-sm text-gray-500">{{ $invoice->client->display_name ?? 'N/A' }}</p>
 </div>
 <div class="text-right">
 <p class="font-medium text-txmain">
 {{ $profile->currency_symbol }}{{ number_format($invoice->total, 2) }}</p>
 <span class="inline-flex px-2 py-1 text-xs rounded-full
 @if($invoice->status === 'paid') bg-green-100 text-green-800
 @elseif($invoice->status === 'overdue') bg-red-100 text-red-800
 @elseif($invoice->status === 'sent') bg-brand-100 text-brand-800
 @else bg-page text-txmain @endif">
 {{ __(ucfirst($invoice->status)) }}
 </span>
 </div>
 </div>
 @empty
 <div class="p-8 text-center text-gray-500">{{ __('No invoices yet') }}</div>
 @endforelse
 </div>
 </div>

 <!-- Recent Payments -->
 <div class="bg-card rounded-xl shadow-sm border border-gray-100">
 <div class="p-6 border-b border-gray-100">
 <h3 class="text-lg font-semibold text-txmain">{{ __('Recent Payments') }}</h3>
 </div>
 <div class="divide-y divide-gray-100">
 @forelse($recentPayments as $payment)
 <div class="p-4 flex items-center justify-between hover:bg-page">
 <div>
 <p class="font-medium text-txmain">{{ $payment->client->display_name ?? 'N/A' }}</p>
 <p class="text-sm text-gray-500">{{ $payment->payment_date->format('M d, Y') }}</p>
 </div>
 <div class="text-right">
 <p class="font-medium text-green-600">
 +{{ $profile->currency_symbol }}{{ number_format($payment->amount, 2) }}</p>
 <p class="text-xs text-gray-500">{{ __(ucfirst($payment->payment_method)) }}</p>
 </div>
 </div>
 @empty
 <div class="p-8 text-center text-gray-500">{{ __('No payments yet') }}</div>
 @endforelse
 </div>
 </div>
 </div>
</div>