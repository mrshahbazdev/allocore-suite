@php $title = __('View Expense'); @endphp

<div>
 <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <div>
 <h2 class="text-2xl font-bold text-txmain">{{ __('View Expense') }}</h2>
 <p class="text-txmain">{{ __('Details of the recorded expenditure') }}</p>
 </div>
 <div class="flex gap-2">
 <a href="{{ route('invoicemaker.expenses.index') }}"
 class="text-txmain hover:text-txmain py-2">{{ __('Back to List') }}</a>
 <a href="{{ route('invoicemaker.expenses.edit', $expense) }}"
 class="bg-brand-600 text-white py-2 px-4 rounded-lg hover:bg-brand-700 shadow-sm transition duration-200 text-center font-medium">
 {{ __('Edit Expense') }}
 </a>
 </div>
 </div>

 <div class="mx-auto bg-card rounded-lg shadow overflow-hidden">
 <div class="p-6">
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
 <div>
 <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Date') }}</h3>
 <p class="text-txmain font-semibold">{{ $expense->date->format('M d, Y') }}</p>
 </div>

 <div>
 <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Payment Source') }}
 </h3>
 @php
 $cashBookEntry = \Modules\InvoiceMaker\Models\CashBookEntry::where('expense_id', $expense->id)->first();
 $source = $cashBookEntry ? $cashBookEntry->source : 'cash';
 @endphp
 <p class="text-txmain font-semibold">{{ $source === 'bank' ? __('Bank') : __('Cash (Kasse)') }}
 </p>
 </div>

 <div>
 <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Amount') }}</h3>
 <p class="text-txmain font-bold text-xl">
 {{ $profile->currency_symbol }}{{ number_format($expense->amount, 2) }}</p>
 </div>

 <div>
 <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Category') }}</h3>
 <span
 class="px-2 py-0.5 rounded bg-page border border-gray-200 text-sm font-semibold text-txmain">
 {{ __($expense->category) }}
 </span>
 </div>

 <div>
 <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">
 {{ __('Partner / Company') }}</h3>
 <p class="text-txmain font-semibold">{{ $expense->partner_name ?: '-' }}</p>
 </div>

 <div>
 <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">
 {{ __('Reference / Invoice #') }}</h3>
 <p class="text-txmain font-semibold">{{ $expense->reference_number ?: '-' }}</p>
 </div>

 <div class="lg:col-span-2">
 <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Description') }}
 </h3>
 <p class="text-txmain font-semibold">{{ $expense->description }}</p>
 </div>

 @if($expense->invoice_id && $expense->invoice)
 <div>
 <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Linked Invoice') }}
 </h3>
 <a href="{{ route('invoicemaker.invoices.show', $expense->invoice) }}"
 class="text-brand-600 hover:text-brand-800 font-semibold flex items-center gap-1">
 [{{ $expense->invoice->invoice_number }}] {{ $expense->invoice->client->name ?? '' }}
 <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
 </svg>
 </a>
 </div>
 @endif
 </div>

 @if($expense->receipt_path)
 <div class="mt-8 border-t border-gray-100 pt-8">
 <h3 class="text-lg font-bold text-txmain mb-4">{{ __('Receipt / Proof of Payment') }}</h3>
 <div class="flex justify-start">
 @if (in_array(strtolower(pathinfo($expense->receipt_path, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif', 'webp']))
 <a href="{{ Storage::url($expense->receipt_path) }}" target="_blank"
 class="rounded-xl overflow-hidden shadow-md border hover:opacity-90 transition inline-block">
 <img src="{{ Storage::url($expense->receipt_path) }}"
 class="max-w-full md:max-w-xl h-auto object-contain">
 </a>
 @else
 <a href="{{ Storage::url($expense->receipt_path) }}" target="_blank"
 class="flex flex-col items-center justify-center p-8 border rounded-xl bg-page hover:bg-page transition min-w-[300px]">
 <svg class="h-16 w-16 text-gray-400 mb-2" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd"
 d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
 clip-rule="evenodd" />
 </svg>
 <span class="text-sm font-semibold text-brand-600">{{ __('View Attached Document') }}</span>
 </a>
 @endif
 </div>
 </div>
 @endif
 </div>
 </div>
</div>