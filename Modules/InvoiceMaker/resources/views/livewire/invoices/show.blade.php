@php $title = $invoice->isEstimate() ? __('View Estimate') : __('View Invoice'); @endphp

<div>
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-txmain">
                {{ $invoice->isEstimate() ? __('Estimate') : __('Invoice') }} {{ __('Details') }}
            </h2>
            <p class="text-txmain">{{ $invoice->isEstimate() ? __('Estimate #') : __('Invoice #') }}: {{ $invoice->invoice_number }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($invoice->status === 'draft')
                <button wire:click="markAsSent" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition disabled:opacity-50">
                    <svg wire:loading wire:target="markAsSent" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ __('Mark as Sent') }}</span>
                </button>
            @endif
            @if(in_array($invoice->status, ['sent', 'overdue']))
                <button wire:click="openPaidModal" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                    <svg wire:loading wire:target="openPaidModal" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ __('Mark as Paid') }}</span>
                </button>
            @endif
            @if($invoice->status === 'sent' && $invoice->due_date->isPast())
                <button wire:click="markAsOverdue" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition disabled:opacity-50">
                    <svg wire:loading wire:target="markAsOverdue" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ __('Mark as Overdue') }}</span>
                </button>
            @endif
            @if(in_array($invoice->status, ['draft', 'sent', 'overdue']))
                <button wire:click="cancelInvoice" wire:loading.attr="disabled"
                    wire:confirm="{{ __('Are you sure you want to cancel this invoice?') }}"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50">
                    <svg wire:loading wire:target="cancelInvoice" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ __('Cancel') }}</span>
                </button>
            @endif
            @if($invoice->status === 'paid')
                <button wire:click="reopenInvoice" wire:loading.attr="disabled"
                    wire:confirm="{{ __('This will reverse all payments and cash book entries for this invoice. Continue?') }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition disabled:opacity-50">
                    <svg wire:loading wire:target="reopenInvoice" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ __('Reopen') }}</span>
                </button>
                <button wire:click="cancelInvoice" wire:loading.attr="disabled"
                    wire:confirm="{{ __('This will cancel the invoice and reverse all payments and cash book entries. Use this for duplicate invoices. Continue?') }}"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50">
                    <svg wire:loading wire:target="cancelInvoice" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ __('Cancel') }}</span>
                </button>
            @endif
            @if($invoice->status === 'cancelled')
                <button wire:click="markAsUnpaid" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition disabled:opacity-50">
                    <svg wire:loading wire:target="markAsUnpaid" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ __('Mark as Unpaid') }}</span>
                </button>
            @endif
            <a href="{{ route('invoicemaker.invoices.download', $invoice) }}"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-page transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                {{ __('Download PDF') }}
            </a>
            <button wire:click="openEmailModal" wire:loading.attr="disabled"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-page transition flex items-center disabled:opacity-50">
                <svg wire:loading wire:target="openEmailModal" class="animate-spin -ml-1 mr-2 h-4 w-4 text-brand-600"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <div wire:loading.remove wire:target="openEmailModal">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <span>{{ __('Email') }} {{ $invoice->isEstimate() ? __('Estimate') : __('Invoice') }}</span>
            </button>
            <div x-data="{ copied: false }" class="relative">
                <button
                    @click="navigator.clipboard.writeText('{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('invoicemaker.public.show', now()->addHour(), ['uuid' => $invoice->uuid]) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-page transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Share Link') }}'"></span>
                </button>
            </div>

            @php
                $docType = $invoice->isEstimate() ? __('estimate') : __('invoice');
                $amount = $invoice->currency_symbol . number_format($invoice->grand_total, 2);
                $publicLink = \Illuminate\Support\Facades\URL::temporarySignedRoute('invoicemaker.public.show', now()->addHour(), ['uuid' => $invoice->uuid]);
                $shareText = __('Here is your :type for :amount from :business. You can view and pay it securely here: :link', [
                    'type' => $docType,
                    'amount' => $amount,
                    'business' => $invoice->business->name,
                    'link' => "\n\n" . $publicLink
                ]);
                $whatsappUrl = 'https://wa.me/?text=' . rawurlencode($shareText);
                $smsUrl = 'sms:?body=' . rawurlencode($shareText);
             @endphp

            <a href="{{ $whatsappUrl }}" target="_blank"
                class="px-4 py-2 border border-green-500 text-green-600 rounded-lg hover:bg-green-50 transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" />
                </svg>
                {{ __('WhatsApp') }}
            </a>

            <a href="{{ $smsUrl }}" target="_blank"
                class="px-4 py-2 border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                    </path>
                </svg>
                {{ __('SMS') }}
            </a>
            @if($invoice->status === 'draft')
                <a href="{{ $invoice->isEstimate() ? route('invoicemaker.estimates.edit', $invoice) : route('invoicemaker.invoices.edit', $invoice) }}"
                    class="bg-card text-txmain py-2 px-4 rounded-lg border border-gray-300 shadow-sm hover:bg-page transition duration-200 text-center font-medium">
                    {{ __('Edit') }} {{ $invoice->isEstimate() ? __('Estimate') : __('Invoice') }}
                </a>
            @endif
            @if($invoice->isEstimate() && $invoice->status !== 'cancelled')
                <button wire:click="convertToInvoice"
                    wire:confirm="{{ __('Convert this estimate to a standard invoice?') }}" wire:loading.attr="disabled"
                    class="inline-flex items-center bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 shadow-sm transition duration-200 text-center font-medium disabled:opacity-50">
                    <svg wire:loading wire:target="convertToInvoice" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ __('Convert to Invoice') }}</span>
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-card rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">{{ __('From') }}</h4>
                        <div>
                            <p class="font-semibold text-txmain">{{ $invoice->business->name }}</p>
                            @if($invoice->business->email)
                            <p class="text-sm text-txmain">{{ $invoice->business->email }}</p>@endif
                            @if($invoice->business->phone)
                            <p class="text-sm text-txmain">{{ $invoice->business->phone }}</p>@endif
                            @if($invoice->business->address)
                                <p class="text-sm text-txmain whitespace-pre-line">{{ $invoice->business->address }}</p>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                            {{ $invoice->isEstimate() ? __('ESTIMATE') : __('INVOICE') }} {{ __('TO') }}
                        </h4>
                        <div>
                            <p class="font-semibold text-txmain">{{ $invoice->client->name }}</p>
                            @if($invoice->client->company_name)
                            <p class="text-sm text-txmain">{{ $invoice->client->company_name }}</p>@endif
                            @if($invoice->client->email)
                            <p class="text-sm text-txmain">{{ $invoice->client->email }}</p>@endif
                            @if($invoice->client->phone)
                            <p class="text-sm text-txmain">{{ $invoice->client->phone }}</p>@endif
                            @if($invoice->client->address)
                                <p class="text-sm text-txmain whitespace-pre-line">{{ $invoice->client->address }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                    {{ $invoice->isEstimate() ? __('ESTIMATE') : __('INVOICE') }} {{ __('INFO') }}
                </h4>
                <div class="space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ $invoice->isEstimate() ? __('Estimate #') : __('Invoice #') }}:</span>
                        <span class="font-medium text-txmain">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ $invoice->isEstimate() ? __('Estimate Date') : __('Invoice Date') }}:</span>
                        <span class="font-medium text-txmain">{{ $invoice->invoice_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ $invoice->isEstimate() ? __('Expiry Date') : __('Due Date') }}:</span>
                        <span class="font-medium text-txmain">{{ $invoice->due_date->format('M d, Y') }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 text-sm font-semibold text-txmain">{{ __('Description') }}
                                </th>
                                <th class="text-right py-3 text-sm font-semibold text-txmain">{{ __('Qty') }}</th>
                                <th class="text-right py-3 text-sm font-semibold text-txmain">{{ __('Price') }}</th>
                                <th class="text-right py-3 text-sm font-semibold text-txmain">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                                <tr class="border-b">
                                    <td class="py-3">
                                        <p class="font-medium text-txmain">{{ $item->description }}</p>
                                        @if($item->tax_rate > 0)
                                        <p class="text-sm text-gray-500">{{ __('Tax') }}: {{ $item->tax_rate }}%</p>@endif
                                    </td>
                                    <td class="py-3 text-right text-txmain">{{ $item->quantity }}</td>
                                    <td class="py-3 text-right text-txmain">
                                        {{ $invoice->currency_symbol }}{{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="py-3 text-right text-txmain font-medium">
                                        {{ $invoice->currency_symbol }}{{ number_format($item->total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="w-64">
                        <div class="flex justify-between py-2">
                            <span class="text-txmain">{{ __('Subtotal') }}:</span>
                            <span
                                class="font-medium">{{ $invoice->currency_symbol }}{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-txmain">{{ __('Tax') }}:</span>
                            <span
                                class="font-medium">{{ $invoice->currency_symbol }}{{ number_format($invoice->tax_total, 2) }}</span>
                        </div>
                        @if($invoice->discount > 0)
                            <div class="flex justify-between py-2">
                                <span class="text-txmain">{{ __('Discount') }}:</span>
                                <span
                                    class="font-medium text-red-600">-{{ $invoice->currency_symbol }}{{ number_format($invoice->discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between py-2 border-t font-bold text-lg">
                            <span>{{ __('Total') }}:</span>
                            <span>{{ $invoice->currency_symbol }}{{ number_format($invoice->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>

                @if($invoice->notes)
                    <div class="mt-6 pt-6 border-t">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">{{ __('Notes') }}</h4>
                        <p class="text-txmain">{{ $invoice->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-card rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Status') }}</h3>
                <div class="flex items-center justify-between">
                    @if($invoice->status === 'sent')
                        <span class="inline-block px-3 py-1 text-sm font-medium rounded-full bg-unpaid-bg text-unpaid-text">
                            {{ __('Unpaid') }}
                        </span>
                    @else
                        <span
                            class="inline-block px-3 py-1 text-sm font-medium rounded-full bg-{{ $invoice->status_color }}-100 text-{{ $invoice->status_color }}-700">
                            {{ __(ucfirst($invoice->status)) }}
                        </span>
                    @endif
                    @if($invoice->last_reminder_sent_at)
                        <span class="text-xs text-gray-500" title="{{ __('Last automated reminder sent') }}">
                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            {{ $invoice->last_reminder_sent_at->diffForHumans() }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="bg-card rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Payment Summary') }}</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-txmain">{{ __('Total') }}:</span>
                        <span
                            class="font-medium">{{ $invoice->currency_symbol }}{{ number_format($invoice->grand_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-txmain">{{ __('Paid') }}:</span>
                        <span
                            class="font-medium text-green-600">{{ $invoice->currency_symbol }}{{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t font-bold">
                        <span>{{ __('Due') }}:</span>
                        <span
                            class="{{ $invoice->amount_due > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $invoice->currency_symbol }}{{ number_format($invoice->amount_due, 2) }}</span>
                    </div>
                </div>
            </div>

            <div
                class="bg-card rounded-lg shadow p-6 border-l-4 {{ $profit >= 0 ? 'border-green-500' : 'border-red-500' }}">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-txmain">{{ __('Job Profitability') }}</h3>
                    <span
                        class="text-xs font-bold px-2 py-1 rounded {{ $profit >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ number_format($margin_percentage, 1) }}% {{ __('Margin') }}
                    </span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-txmain">{{ __('Revenue') }}:</span>
                        <span
                            class="font-medium text-txmain">{{ $invoice->currency_symbol }}{{ number_format($invoice->grand_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-txmain">{{ __('Linked Costs') }}:</span>
                        <span
                            class="font-medium text-red-600">-{{ $invoice->currency_symbol }}{{ number_format($total_expenses, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t font-bold text-base">
                        <span>{{ __('Net Profit') }}:</span>
                        <span
                            class="{{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $invoice->currency_symbol }}{{ number_format($profit, 2) }}</span>
                    </div>

                    @if($invoice->expenses->count() > 0)
                        <div class="mt-4 pt-4 border-t">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                {{ __('Linked Expenses') }}
                            </h4>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                @foreach($invoice->expenses as $expense)
                                    <div
                                        class="flex justify-between items-center text-xs p-2 bg-page rounded border border-gray-100">
                                        <div class="truncate mr-2">
                                            <p class="font-semibold text-txmain truncate">{{ $expense->description }}</p>
                                            <p class="text-gray-500 italic">{{ $expense->date->format('d.m.Y') }}</p>
                                        </div>
                                        <span
                                            class="font-bold text-red-600 whitespace-nowrap">-{{ $invoice->currency_symbol }}{{ number_format($expense->amount, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="mt-4 p-3 bg-page rounded text-center">
                            <p class="text-xs text-gray-500 italic">{{ __('No expenses linked to this invoice yet.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($invoice->amount_due > 0)
                <div class="bg-card rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Record Payment') }}</h3>
                    <form wire:submit="recordPayment">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Amount') }}</label>
                            <input type="number" step="0.01" wire:model="payment_amount"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                                placeholder="0.00">
                            @error('payment_amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Method') }}</label>
                            <select wire:model="payment_method"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                                <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                <option value="credit_card">{{ __('Credit Card') }}</option>
                                <option value="cash">{{ __('Cash') }}</option>
                                <option value="check">{{ __('Check') }}</option>
                                <option value="paypal">{{ __('PayPal') }}</option>
                                <option value="stripe">{{ __('Stripe') }}</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Date') }}</label>
                            <input type="date" wire:model="payment_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            @error('payment_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Notes (optional)') }}</label>
                            <textarea wire:model="payment_notes" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                            <svg wire:loading wire:target="recordPayment" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>{{ __('Record Payment') }}</span>
                        </button>
                    </form>
                </div>
            @endif

            @if($invoice->payments->count() > 0)
                <div class="bg-card rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Payment History') }}</h3>
                    <div class="space-y-3">
                        @foreach($invoice->payments as $payment)
                            <div class="flex justify-between items-start pb-3 border-b last:border-0">
                                <div>
                                    <p class="font-medium">
                                        {{ $invoice->currency_symbol }}{{ number_format($payment->amount, 2) }}
                                    </p>
                                    <p class="text-sm text-gray-500">{{ $payment->date->format('M d, Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-txmain">{{ __(ucfirst(str_replace('_', ' ', $payment->method))) }}
                                    </p>
                                    <button wire:click="deletePayment({{ $payment->id }})"
                                        wire:confirm="{{ __('Delete this payment?') }}"
                                        class="text-xs text-red-600 hover:text-red-700">{{ __('Delete') }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Email Log --}}
            @php $emailLogs = $invoice->emailLogs()->latest()->get(); @endphp
            <div class="bg-card rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-txmain mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    {{ __('Email Log') }}
                </h3>
                @if($emailLogs->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No emails have been sent for this invoice yet.') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach($emailLogs as $log)
                            <div class="flex justify-between items-start pb-3 border-b last:border-0">
                                <div>
                                    <div class="flex items-center gap-2">
                                        @if($log->status === 'sent')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                {{ __('Sent') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                {{ __('Failed') }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            @if($log->type === 'manual')
                                                {{ __('Manual') }}
                                            @elseif($log->type === 'scheduled')
                                                {{ __('Automatic') }}
                                            @else
                                                {{ __('Reminder') }}
                                            @endif
                                        </span>
                                    </div>
                                    <p class="text-sm text-txmain mt-1">{{ $log->recipient_email }}</p>
                                    @if($log->subject)
                                        <p class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ $log->subject }}</p>
                                    @endif
                                    @if($log->error_message)
                                        <p class="text-xs text-red-500 mt-0.5">{{ $log->error_message }}</p>
                                    @endif
                                </div>
                                <div class="text-right text-sm text-gray-500 whitespace-nowrap">
                                    {{ $log->created_at->format('M d, Y') }}
                                    <br>
                                    <span class="text-xs">{{ $log->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

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
                            <input type="date" wire:model="payment_date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                            @error('payment_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-sm transition font-bold disabled:opacity-50 flex items-center gap-2">
                            <svg wire:loading wire:target="markAsPaid" class="animate-spin h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>{{ __('Save Payment') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    @if($showEmailModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="bg-card rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all">
                <div class="p-6 border-b flex justify-between items-center bg-page">
                    <h3 class="text-xl font-bold text-txmain">{{ __('Send Email') }}</h3>
                    <button wire:click="closeEmailModal()" class="text-gray-400 hover:text-txmain">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="sendEmail">
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-txmain">
                            {{ __('Customize the email subject and body below before sending. Placeholders have already been replaced.') }}
                        </p>

                        <div>
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Subject') }} *</label>
                            <input type="text" wire:model="emailSubject"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                            @error('emailSubject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-txmain mb-1">{{ __('Message') }} *</label>
                            <textarea wire:model="emailBody" rows="10"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500"></textarea>
                            @error('emailBody') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="p-6 bg-page border-t flex justify-end gap-3">
                        <button type="button" wire:click="closeEmailModal()"
                            class="px-4 py-2 text-txmain hover:text-txmain font-medium">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 shadow-sm transition font-bold disabled:opacity-50 flex items-center gap-2">
                            <svg wire:loading wire:target="sendEmail" class="animate-spin h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>{{ __('Send Email') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>