@php $title = __('Bank Reconciliation'); @endphp

<div>
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-txmain tracking-tight">{{ __('Bank Reconciliation') }}</h2>
            <p class="text-gray-500 font-medium">{{ __('Import your bank statement to automatically match transactions to your Cash Book.') }}</p>
        </div>
        <button wire:click="cancel" class="px-4 py-2 bg-card border border-gray-200 text-txmain rounded-lg shadow-sm hover:bg-page transition-colors font-bold text-sm">
            {{ __('Back to Cash Book') }}
        </button>
    </div>

    <div class="bg-card rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        
        <!-- Progress Bar -->
        <div class="flex items-center justify-between px-8 py-4 bg-page/50 border-b border-gray-200">
            <div class="flex items-center space-x-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $step >= 1 ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-500' }} font-bold text-sm">1</span>
                <span class="{{ $step >= 1 ? 'text-brand-600 font-bold' : 'text-gray-400 font-medium' }}">{{ __('Upload File') }}</span>
            </div>
            <div class="flex-1 border-t-2 {{ $step >= 2 ? 'border-brand-600' : 'border-gray-200' }} mx-4"></div>
            <div class="flex items-center space-x-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $step >= 2 ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-500' }} font-bold text-sm">2</span>
                <span class="{{ $step >= 2 ? 'text-brand-600 font-bold' : 'text-gray-400 font-medium' }}">{{ __('Map Columns') }}</span>
            </div>
            <div class="flex-1 border-t-2 {{ $step >= 3 ? 'border-brand-600' : 'border-gray-200' }} mx-4"></div>
            <div class="flex items-center space-x-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $step >= 3 ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-500' }} font-bold text-sm">3</span>
                <span class="{{ $step >= 3 ? 'text-brand-600 font-bold' : 'text-gray-400 font-medium' }}">{{ __('Review & Import') }}</span>
            </div>
        </div>

        <div class="p-8">
            {{-- STEP 1: UPLOAD --}}
            @if($step === 1)
                <div class="max-w-xl mx-auto text-center py-8">
                    <svg class="w-16 h-16 text-brand-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-xl font-bold text-txmain mb-2">{{ __('Upload Bank Statement (CSV)') }}</h3>
                    <p class="text-gray-500 text-sm mb-8">{{ __('Export your statement from your bank as a CSV file and upload it here. We will help you map the columns.') }}</p>

                    <div class="mb-6">
                        <input type="file" wire:model="csvFile" class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition-colors border border-gray-200 rounded-xl cursor-pointer">
                        @error('csvFile') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="csvFile" class="mt-2 text-sm text-brand-600 font-medium">{{ __('Uploading...') }}</div>
                    </div>

                    <button wire:click="processUpload" wire:loading.attr="disabled" class="w-full py-3 bg-brand-600 text-white font-bold rounded-xl shadow-lg shadow-brand-600/30 hover:bg-brand-700 transition disabled:opacity-50">
                        {{ __('Next: Map Columns') }}
                    </button>
                </div>
            @endif

            {{-- STEP 2: MAPPING --}}
            @if($step === 2)
                <div class="max-w-3xl mx-auto">
                    <h3 class="text-xl font-bold text-txmain mb-6">{{ __('Map CSV Columns') }}</h3>
                    <p class="text-gray-500 text-sm mb-8">{{ __('Please select which column from your CSV file corresponds to the required system fields.') }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Date -->
                        <div class="bg-page/50 p-6 rounded-xl border border-gray-200">
                            <label class="block text-sm font-bold text-txmain mb-2">{{ __('Date Column') }} <span class="text-red-500">*</span></label>
                            <select wire:model="mapDate" class="w-full border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500">
                                <option value="">{{ __('Select Column...') }}</option>
                                @foreach($availableHeaders as $header)
                                    <option value="{{ $header }}">{{ $header }}</option>
                                @endforeach
                            </select>
                            @error('mapDate') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Description/Reference -->
                        <div class="bg-page/50 p-6 rounded-xl border border-gray-200">
                            <label class="block text-sm font-bold text-txmain mb-2">{{ __('Description/Reference') }} <span class="text-red-500">*</span></label>
                            <select wire:model="mapDescription" class="w-full border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500">
                                <option value="">{{ __('Select Column...') }}</option>
                                @foreach($availableHeaders as $header)
                                    <option value="{{ $header }}">{{ $header }}</option>
                                @endforeach
                            </select>
                            @error('mapDescription') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Amount -->
                        <div class="bg-page/50 p-6 rounded-xl border border-gray-200">
                            <label class="block text-sm font-bold text-txmain mb-2">{{ __('Amount Column') }} <span class="text-red-500">*</span></label>
                            <select wire:model="mapAmount" class="w-full border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500">
                                <option value="">{{ __('Select Column...') }}</option>
                                @foreach($availableHeaders as $header)
                                    <option value="{{ $header }}">{{ $header }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-gray-500 mt-2">{{ __('Positive amounts = Income. Negative = Expenses.') }}</p>
                            @error('mapAmount') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-8">
                        <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">{{ __('Data Preview (First 3 Rows)') }}</h4>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        @foreach($csvHeaders as $header)
                                            <th class="px-3 py-2 text-gray-700 font-bold whitespace-nowrap">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($csvPreview as $row)
                                        <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                                            @foreach($row as $cell)
                                                <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ Str::limit($cell, 30) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <button wire:click="$set('step', 1)" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
                            {{ __('Back') }}
                        </button>
                        <button wire:click="processMapping" wire:loading.attr="disabled" class="px-8 py-3 bg-brand-600 text-white font-bold rounded-xl shadow-lg shadow-brand-600/30 hover:bg-brand-700 transition disabled:opacity-50">
                            {{ __('Scan & Auto-Match') }}
                        </button>
                    </div>
                </div>
            @endif

            {{-- STEP 3: REVIEW & FINALIZE --}}
            @if($step === 3)
                <div>
                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-txmain mb-1">{{ __('Review Matches') }}</h3>
                            <p class="text-gray-500 text-sm">{{ __('We automatically scanned your open invoices and expenses. Review the matches below before importing.') }}</p>
                        </div>
                        <div class="flex items-center space-x-2 text-sm font-medium">
                            <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-700 rounded"><span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span> {{ __('Exact Match') }}</span>
                            <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 rounded"><span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span> {{ __('Misc / Unmatched') }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-xl mb-8 shadow-sm">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="bg-page border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[11px] font-bold">
                                <tr>
                                    <th class="px-4 py-3 text-center w-12">{{ __('Import') }}</th>
                                    <th class="px-4 py-3">{{ __('Date') }}</th>
                                    <th class="px-4 py-3">{{ __('Bank Description') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Amount') }}</th>
                                    <th class="px-4 py-3">{{ __('System Match') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($reconciliationRows as $index => $row)
                                    <tr class="hover:bg-page transition-colors {{ $row['status'] === 'exact' ? 'bg-green-50/20' : '' }}">
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" wire:model="reconciliationRows.{{ $index }}.selected" class="rounded text-brand-600 focus:ring-brand-500">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-txmain font-medium">
                                            {{ \Carbon\Carbon::parse($row['raw_date'])->format('d.m.Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-txmain text-xs max-w-xs truncate cursor-help" title="{{ $row['description'] }}">
                                            {{ $row['description'] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right font-bold {{ $row['amount'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $row['amount'] > 0 ? '+' : '' }}{{ number_format($row['amount'], 2, ',', '.') }} €
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($row['status'] === 'exact' && $row['matched_type'] === 'invoice')
                                                <div class="inline-flex items-center px-2.5 py-1.5 rounded bg-green-100 text-green-800 text-xs font-bold border border-green-200">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Invoice Matched
                                                </div>
                                            @elseif($row['status'] === 'exact' && $row['matched_type'] === 'expense')
                                                <div class="inline-flex items-center px-2.5 py-1.5 rounded bg-green-100 text-green-800 text-xs font-bold border border-green-200">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Expense Matched
                                                </div>
                                            @else
                                                <div class="flex items-center">
                                                    <select wire:model="reconciliationRows.{{ $index }}.matched_type" class="text-xs border-gray-300 rounded focus:ring-brand-500 py-1 pl-2 pr-6">
                                                        <option value="misc">{{ __('Misc / Unmatched') }}</option>
                                                        <option value="invoice">{{ __('Invoice Payment') }}</option>
                                                        <option value="expense">{{ __('System Expense') }}</option>
                                                    </select>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            {{ __('No valid transactions found to parse/match.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between items-center bg-page p-6 rounded-xl border border-gray-200">
                        <div>
                            <p class="text-sm text-gray-500">{{ __('By confirming, matched invoices will be marked as paid, and Cash Book entries will be generated automatically.') }}</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <button wire:click="$set('step', 2)" class="px-6 py-3 bg-white border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">
                                {{ __('Back') }}
                            </button>
                            <button wire:click="finalizeReconciliation" wire:loading.attr="disabled" class="px-8 py-3 bg-brand-600 text-white font-bold rounded-xl shadow-lg shadow-brand-600/30 hover:bg-brand-700 transition disabled:opacity-50">
                                <span wire:loading.remove wire:target="finalizeReconciliation">{{ __('Confirm & Import') }}</span>
                                <span wire:loading wire:target="finalizeReconciliation">{{ __('Importing...') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
