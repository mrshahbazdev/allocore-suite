@php $title = __('Invoices'); @endphp

<div>
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-txmain">{{ __('Invoices') }}</h2>
            <p class="text-txmain">{{ __('Manage your invoices') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('invoicemaker.invoices.create', ['quick' => 1]) }}"
                class="bg-card text-txmain py-2 px-4 rounded-lg border border-gray-300 shadow-sm hover:bg-page transition duration-200 text-center font-medium">
                ⚡ {{ __('Quick Invoice') }}
            </a>
            <a href="{{ route('invoicemaker.invoices.create') }}"
                class="bg-brand-600 text-white py-2 px-4 rounded-lg hover:bg-brand-700 shadow-sm transition duration-200 text-center font-medium">
                + {{ __('Create Invoice') }}
            </a>
        </div>
    </div>

    <div class="bg-card rounded-lg shadow">
        <div class="p-4 border-b flex flex-col md:flex-row gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search invoices...') }}"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
            <select wire:model.live="status"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="draft">{{ __('Draft') }}</option>
                <option value="sent">{{ __('Sent') }}</option>
                <option value="paid">{{ __('Paid') }}</option>
                <option value="overdue">{{ __('Overdue') }}</option>
                <option value="cancelled">{{ __('Cancelled') }}</option>
            </select>
        </div>

        @if(count($selected) > 0)
            <div class="p-4 border-b bg-page flex flex-col sm:flex-row gap-3 sm:items-center">
                <span class="text-sm text-txmain">
                    {{ count($selected) }} {{ __('selected') }}
                </span>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="removeFromRecurringLoop"
                        wire:confirm="{{ __('Are you sure you want to remove the selected invoices from the recurring loop?') }}"
                        class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm font-medium">
                        {{ __('Remove from loop') }}
                    </button>
                    <button wire:click="openBulkRecurringModal"
                        class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 text-sm font-medium">
                        {{ __('Re-insert into loop') }}
                    </button>
                </div>
            </div>
            @if($invoices->total() > count($selected))
                <div class="px-4 py-2 border-b bg-page text-sm">
                    <button wire:click="selectAllMatching" class="text-brand-600 hover:text-brand-700 font-medium">
                        {{ __('Select all :count results', ['count' => $invoices->total()]) }}
                    </button>
                </div>
            @endif
        @endif

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b bg-page">
                        <th class="py-3 px-4 text-left w-10">
                            <input type="checkbox" wire:click="toggleSelectAll"
                                @checked(collect($invoices->pluck('id'))->every(fn ($id) => in_array($id, $selected)))
                                class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500">
                        </th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-txmain cursor-pointer"
                            wire:click="sortBy('invoice_number')">
                            {{ __('Invoice') }}
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
                        <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Due Date') }}</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Amount') }}</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Status') }}</th>
                        <th class="text-right py-3 px-4 text-sm font-semibold text-txmain">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr class="border-b hover:bg-page">
                            <td class="py-3 px-4">
                                <input type="checkbox" wire:model.live="selected" value="{{ $invoice->id }}"
                                    class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500">
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('invoicemaker.invoices.show', $invoice) }}"
                                        class="text-brand-600 hover:text-brand-700 font-medium">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                    @if($invoice->is_recurring)
                                        <span
                                            class="inline-flex items-center rounded-md bg-purple-50 px-1.5 py-0.5 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10"
                                            title="{{ __('Recurring Invoice') }}">
                                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            R
                                        </span>
                                    @endif
                                    @if($invoice->emails_sent_count > 0)
                                        <span
                                            class="inline-flex items-center rounded-md bg-green-50 px-1.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20"
                                            title="{{ __('Email sent') }} ({{ $invoice->emails_sent_count }}x)">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-txmain">{{ $invoice->client->name }}</td>
                            <td class="py-3 px-4 text-txmain">{{ $invoice->invoice_date->format('M d, Y') }}</td>
                            <td class="py-3 px-4 text-txmain">{{ $invoice->due_date->format('M d, Y') }}</td>
                            <td class="py-3 px-4 text-txmain font-medium">
                                {{ $invoice->currency_symbol }}{{ number_format($invoice->grand_total, 2) }}
                            </td>
                            <td class="py-3 px-4">
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
                            <td class="py-3 px-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('invoicemaker.invoices.show', $invoice) }}"
                                        class="text-brand-600 hover:text-brand-700 text-sm font-medium">{{ __('View') }}</a>
                                    @if($invoice->status === 'cancelled')
                                        <button wire:click="markAsUnpaid({{ $invoice->id }})"
                                            class="text-brand-600 hover:text-brand-700 text-sm font-medium">{{ __('Mark as Unpaid') }}</button>
                                    @endif
                                    @if($invoice->status === 'paid')
                                        <button wire:click="reopenInvoice({{ $invoice->id }})"
                                            wire:confirm="{{ __('This will reverse all payments and cash book entries for this invoice. Continue?') }}"
                                            class="text-yellow-600 hover:text-yellow-700 text-sm font-medium">{{ __('Reopen') }}</button>
                                        <button wire:click="cancelPaidInvoice({{ $invoice->id }})"
                                            wire:confirm="{{ __('This will cancel the invoice and reverse all payments and cash book entries. Use this for duplicate invoices. Continue?') }}"
                                            class="text-red-600 hover:text-red-700 text-sm font-medium">{{ __('Cancel') }}</button>
                                    @endif
                                    @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                                        <button wire:click="openPaidModal({{ $invoice->id }})"
                                            class="text-green-600 hover:text-green-700 text-sm font-medium">{{ __('Paid') }}</button>
                                    @endif
                                    @if($invoice->status === 'draft')
                                        <a href="{{ route('invoicemaker.invoices.edit', $invoice) }}"
                                            class="text-brand-600 hover:text-brand-700 text-sm font-medium">{{ __('Edit') }}</a>
                                    @endif
                                    <button wire:click="delete({{ $invoice->id }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this invoice?') }}"
                                        class="text-red-600 hover:text-red-700 text-sm font-medium">{{ __('Delete') }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                {{ __('No invoices found.') }} <a href="{{ route('invoicemaker.invoices.create') }}"
                                    class="text-brand-600 hover:text-brand-700">{{ __('Create your first invoice') }}</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t flex justify-between items-center">
                <span class="text-sm text-txmain">
                    {{ __('Showing') }} {{ $invoices->firstItem() }} {{ __('to') }} {{ $invoices->lastItem() }}
                    {{ __('of') }} {{ $invoices->total() }} {{ __('results') }}
                </span>
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <!-- Mark as Paid Modal -->
    @if($showPaidModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="bg-card rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="p-6 border-b flex justify-between items-center bg-page">
                    <h3 class="text-xl font-bold text-txmain">{{ __('Mark as Paid') }}</h3>
                    <button wire:click="closePaidModal()" class="text-gray-400 hover:text-txmain">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="markAsPaid">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Payment Date') }} *</label>
                            <input type="date" wire:model="paymentDate"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                            @error('paymentDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Source') }} *</label>
                            <select wire:model="paymentSource"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                                <option value="bank">{{ __('Bank') }}</option>
                                <option value="cash">{{ __('Cash (Kasse)') }}</option>
                            </select>
                            @error('paymentSource') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Description') }} *</label>
                            <input type="text" wire:model="paymentDescription"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                            @error('paymentDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="p-6 bg-page border-t flex justify-end gap-3">
                        <button type="button" wire:click="closePaidModal()"
                            class="px-4 py-2 text-txmain hover:text-txmain font-medium">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-sm transition font-bold">
                            {{ __('Save Payment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Bulk Re-insert into Loop Modal -->
    @if($showBulkRecurringModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="bg-card rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="p-6 border-b flex justify-between items-center bg-page">
                    <h3 class="text-xl font-bold text-txmain">{{ __('Re-insert into Recurring Loop') }}</h3>
                    <button wire:click="closeBulkRecurringModal()" class="text-gray-400 hover:text-txmain">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="insertIntoRecurringLoop">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Frequency') }} *</label>
                            <select wire:model="bulkRecurringFrequency"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                                <option value="weekly">{{ __('Weekly') }}</option>
                                <option value="monthly">{{ __('Monthly') }}</option>
                                <option value="quarterly">{{ __('Quarterly') }}</option>
                                <option value="yearly">{{ __('Yearly') }}</option>
                            </select>
                            @error('bulkRecurringFrequency') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Next Run Date') }} *</label>
                            <input type="date" wire:model="bulkRecurringNextRunDate"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                            @error('bulkRecurringNextRunDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ __('This will affect :count invoice(s).', ['count' => count($selected)]) }}
                        </p>
                    </div>
                    <div class="p-6 bg-page border-t flex justify-end gap-3">
                        <button type="button" wire:click="closeBulkRecurringModal()"
                            class="px-4 py-2 text-txmain hover:text-txmain font-medium">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow-sm transition font-bold">
                            {{ __('Re-insert') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>