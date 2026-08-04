@php $title = __('Edit Invoice'); @endphp

<div>
 <div class="mb-8 flex items-center justify-between">
 <div>
 <h2 class="text-2xl font-bold text-txmain">{{ __('Edit Invoice') }}</h2>
 <p class="text-txmain">{{ $invoice->invoice_number }}</p>
 </div>
 </div>

 <div class="bg-card rounded-lg shadow p-6 mb-6">
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Client') }} *</label>
 <select wire:model.live="client_id"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @foreach($this->clients as $client)
 <option value="{{ $client->id }}">{{ $client->name }} @if($client->company_name)
 ({{ $client->company_name }}) @endif</option>
 @endforeach
 </select>
 @error('client_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Currency Override') }}</label>
 <select wire:model.live="currency"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="USD">USD - {{ __('US Dollar') }}</option>
 <option value="EUR">EUR - {{ __('Euro') }}</option>
 <option value="GBP">GBP - {{ __('British Pound') }}</option>
 <option value="CAD">CAD - {{ __('Canadian Dollar') }}</option>
 <option value="AUD">AUD - {{ __('Australian Dollar') }}</option>
 <option value="JPY">JPY - {{ __('Japanese Yen') }}</option>
 <option value="PKR">PKR - {{ __('Pakistani Rupee') }}</option>
 <option value="INR">INR - {{ __('Indian Rupee') }}</option>
 <option value="AED">AED - {{ __('UAE Dirham') }}</option>
 </select>
 @error('currency') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Invoice Date') }} *</label>
 <input type="date" wire:model="invoice_date"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('invoice_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Due Date') }} *</label>
 <input type="date" wire:model="due_date"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('due_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>

 <div class="mt-6">
 <div class="flex items-center justify-between mb-1">
 <label class="block text-sm font-medium text-txmain">{{ __('Notes') }}</label>
 <button type="button" wire:click="generateNotesWithAi"
 class="text-xs font-semibold text-brand-600 hover:text-brand-800 flex items-center transition bg-brand-50 px-2 py-1 rounded-md border border-brand-100">
 <svg wire:loading wire:target="generateNotesWithAi"
 class="animate-spin -ml-1 mr-1 h-3 w-3 text-brand-600" fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
 </circle>
 <path class="opacity-75" fill="currentColor"
 d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
 </path>
 </svg>
 <span wire:loading.remove wire:target="generateNotesWithAi" class="mr-1">✨</span>
 <span wire:loading.remove wire:target="generateNotesWithAi">{{ __('Write with AI') }}</span>
 <span wire:loading wire:target="generateNotesWithAi">{{ __('Generating...') }}</span>
 </button>
 </div>
 <textarea wire:model="notes" rows="2"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"></textarea>
 @if(session('error'))
 <p class="mt-1 text-xs text-red-600">{{ session('error') }}</p> @endif
 </div>

 <div class="mt-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Payment Terms & Instructions') }}</label>
 <textarea wire:model="payment_terms" rows="3"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('Bank details, wire instructions, etc...') }}"></textarea>
 </div>

 <div class="mt-8 pt-6 border-t border-gray-100">
 <div class="flex items-center justify-between mb-4">
 <div>
 <h4 class="text-sm font-semibold text-txmain">{{ __('Recurring Invoice') }}</h4>
 <p class="text-xs text-gray-500">{{ __('Automatically generate this invoice on a schedule') }}</p>
 </div>
 <button type="button" wire:click="$set('is_recurring', {{ !$is_recurring ? 'true' : 'false' }})"
 class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $is_recurring ? 'bg-brand-600' : 'bg-gray-200' }}">
 <span
 class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-card shadow ring-0 transition duration-200 ease-in-out {{ $is_recurring ? 'translate-x-5' : 'translate-x-0' }}"></span>
 </button>
 </div>

 @if($is_recurring)
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-transition>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Frequency') }}</label>
 <select wire:model="recurring_frequency"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="weekly">{{ __('Weekly') }}</option>
 <option value="monthly">{{ __('Monthly') }}</option>
 <option value="quarterly">{{ __('Quarterly') }}</option>
 <option value="yearly">{{ __('Yearly') }}</option>
 </select>
 @error('recurring_frequency') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Next Run Date') }}</label>
 <input type="date" wire:model="next_run_date"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('next_run_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>
 @endif
 </div>
 </div>

 <div class="bg-card rounded-lg shadow p-6 mb-6">
 <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Items') }}</h3>

 <div class="mb-4">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Search Products') }}</label>
 <div class="relative">
 <input type="text" wire:model.live.debounce.300ms="product_search"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('Type to search products...') }}">
 @if($this->products->count() > 0)
 <div
 class="absolute z-10 w-full mt-1 bg-card border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
 @foreach($this->products as $product)
 <button wire:click="selectProduct({{ $product->id }})"
 class="w-full text-left px-4 py-2 hover:bg-page">
 <div class="font-medium">{{ $product->name }}</div>
 <div class="text-sm text-gray-500 flex justify-between items-center">
 <span>{{ $this->currency_symbol }}{{ number_format((float) $product->price, 2) }} /
 {{ __($product->unit) }}</span>
 @if($product->manage_stock)
 <span
 class="text-xs font-semibold {{ $product->stock_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
 {{ $product->stock_quantity }} {{ __('in stock') }}
 </span>
 @endif
 </div>
 </button>
 @endforeach
 </div>
 @endif
 </div>
 </div>

 <div class="space-y-4 mb-4">
 @forelse($items as $index => $item)
 <div class="p-4 border rounded-lg bg-page">
 <div class="flex justify-between items-start mb-3">
 <span class="text-sm font-medium">{{ __('Item') }} {{ $index + 1 }}</span>
 <button wire:click="removeItem({{ $index }})"
 class="text-red-600 hover:text-red-700 text-sm">{{ __('Remove') }}</button>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 <div class="md:col-span-2">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Description') }} *</label>
 <textarea wire:model="items.{{ $index }}.description" rows="2"
 class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm"></textarea>
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Quantity') }} *</label>
 <input type="number" step="0.01" wire:model.live="items.{{ $index }}.quantity"
 wire:change="updateItemTotal({{ $index }})"
 class="w-full px-3 py-2 border {{ (isset($item['manage_stock']) && $item['manage_stock'] && $item['quantity'] > ($item['stock_quantity'] ?? 0)) ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-500' : 'border-gray-300 focus:ring-brand-500' }} rounded-lg text-sm">
 @if(isset($item['manage_stock']) && $item['manage_stock'])
 <p
 class="mt-1 text-xs {{ $item['quantity'] > ($item['stock_quantity'] ?? 0) ? 'text-red-700 font-bold' : 'text-gray-500' }}">
 {{ __('Current Stock') }}: {{ $item['stock_quantity'] ?? 0 }}
 </p>
 @endif
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Unit Price') }} *</label>
 <input type="number" step="0.01" wire:model.live="items.{{ $index }}.unit_price"
 wire:change="updateItemTotal({{ $index }})"
 class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm">
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Tax Rate') }} (%)</label>
 <input type="number" step="0.01" wire:model.live="items.{{ $index }}.tax_rate"
 wire:change="updateItemTotal({{ $index }})"
 class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm">
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Total') }}</label>
 <div class="px-3 py-2 bg-page rounded-lg text-sm font-medium">
 {{ $this->currency_symbol }}{{ number_format((float) $item['total'], 2) }}
 </div>
 </div>
 </div>
 </div>
 @empty
 <div class="text-center py-8 text-gray-500">
 {{ __('No items. Click "Add Item" to start.') }}
 </div>
 @endforelse
 </div>

 <button wire:click="addItem" type="button"
 class="w-full border-2 border-dashed border-gray-300 rounded-lg py-3 text-txmain hover:border-brand-500 hover:text-brand-600 transition">
 + {{ __('Add Item') }}
 </button>
 </div>

 <div class="bg-card rounded-lg shadow p-6 mb-6">
 <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Summary') }}</h3>

 <div class="space-y-2">
 <div class="flex justify-between">
 <span>{{ __('Subtotal') }}:</span>
 <span>{{ $this->currency_symbol }}{{ number_format((float) $this->totals['subtotal'], 2) }}</span>
 </div>
 <div class="flex justify-between">
 <span>{{ __('Tax') }}:</span>
 <span>{{ $this->currency_symbol }}{{ number_format((float) $this->totals['tax_total'], 2) }}</span>
 </div>
 <div class="flex justify-between font-bold text-lg pt-2 border-t">
 <span>{{ __('Total') }}:</span>
 <span>{{ $this->currency_symbol }}{{ number_format((float) $this->totals['grand_total'], 2) }}</span>
 </div>
 </div>
 </div>

 <div class="flex justify-between">
 <a href="{{ route('invoicemaker.invoices.show', $invoice) }}"
 class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-page transition">
 {{ __('Cancel') }}
 </a>
 <button wire:click="save" wire:loading.attr="disabled"
 class="inline-flex items-center px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition disabled:opacity-50">
 <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
 viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor"
 d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
 </path>
 </svg>
 <span>{{ __('Update Invoice') }}</span>
 </button>
 </div>
</div>