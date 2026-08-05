@php $title = __('Create Estimate'); @endphp

<div>
 <div class="mb-8 flex items-center justify-between">
 <div>
 <h2 class="text-2xl font-bold text-txmain">{{ __('Create Estimate') }}</h2>
 <p class="text-txmain">{{ __('Step') }} {{ $step }} {{ __('of') }} 4</p>
 </div>
 <div class="flex space-x-2">
 <div class="h-2 w-8 rounded-full {{ $step >= 1 ? 'bg-brand-600' : 'bg-gray-200' }}"></div>
 <div class="h-2 w-8 rounded-full {{ $step >= 2 ? 'bg-brand-600' : 'bg-gray-200' }}"></div>
 <div class="h-2 w-8 rounded-full {{ $step >= 3 ? 'bg-brand-600' : 'bg-gray-200' }}"></div>
 <div class="h-2 w-8 rounded-full {{ $step >= 4 ? 'bg-brand-600' : 'bg-gray-200' }}"></div>
 </div>
 </div>

 <!-- Step 1: Select Client -->
 @if($step === 1)
 <div x-transition>
 <div class="bg-card rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Select Client') }}</h3>
 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Client') }} *</label>
 <select wire:model="client_id"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="">{{ __('Select a client...') }}</option>
 @foreach($this->clients as $client)
 <option value="{{ $client->id }}">{{ $client->name }} @if($client->company_name)
 ({{ $client->company_name }}) @endif</option>
 @endforeach
 </select>
 @error('client_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>
 </div>
 @endif

 <!-- Step 2: Add Items -->
 @if($step === 2)
 <div x-transition>
 <div class="bg-card rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Add Items') }}</h3>

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
 <div class="text-sm text-gray-500">
 {{ $this->currency_symbol }}{{ number_format((float) $product->price, 2) }} /
 {{ __($product->unit) }}
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
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Description') }}
 *</label>
 <textarea wire:model="items.{{ $index }}.description" rows="2"
 class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm"></textarea>
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Quantity') }} *</label>
 <input type="number" step="0.01" wire:model.live="items.{{ $index }}.quantity"
 wire:change="updateItemTotal({{ $index }})"
 class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm">
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
 {{ __('No items added yet. Click "Add Item" to start.') }}
 </div>
 @endforelse
 </div>

 <button wire:click="addItem" type="button"
 class="w-full border-2 border-dashed border-gray-300 rounded-lg py-3 text-txmain hover:border-brand-500 hover:text-brand-600 transition">
 + {{ __('Add Item') }}
 </button>
 </div>
 </div>
 @endif

 <!-- Step 3: Estimate Details -->
 @if($step === 3)
 <div x-transition>
 <div class="bg-card rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Estimate Details') }}</h3>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Estimate Date') }} *</label>
 <input type="date" wire:model="invoice_date"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('invoice_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Expiration Date') }} *</label>
 <input type="date" wire:model="due_date"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('due_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Currency') }}</label>
 <select wire:model="currency"
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
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Discount') }}</label>
 <input type="number" step="0.01" wire:model.live="discount"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="0.00">
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Notes') }}</label>
 <textarea wire:model="notes" rows="3"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('Terms, thank you note, etc...') }}"></textarea>
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Template') }}</label>
 <select wire:model="template_id"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="">{{ __('Select a template...') }}</option>
 @foreach($this->templates as $t)
 <option value="{{ $t->id }}">{{ $t->name }}</option>
 @endforeach
 </select>
 @error('template_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>
 </div>
 </div>
 @endif

 <!-- Step 4: Review & Save -->
 @if($step === 4)
 <div x-transition>
 <div class="bg-card rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Review Estimate') }}</h3>

 <div class="space-y-4">
 <div class="flex justify-between py-2 border-b">
 <span class="font-medium">{{ __('Client') }}:</span>
 <span>{{ $this->clients->find($client_id)?->name }}</span>
 </div>
 <div class="flex justify-between py-2 border-b">
 <span class="font-medium">{{ __('Estimate Date') }}:</span>
 <span>{{ \Carbon\Carbon::parse($invoice_date)->format('M d, Y') }}</span>
 </div>
 <div class="flex justify-between py-2 border-b">
 <span class="font-medium">{{ __('Expiration Date') }}:</span>
 <span>{{ \Carbon\Carbon::parse($due_date)->format('M d, Y') }}</span>
 </div>

 <div class="py-4">
 <h4 class="font-semibold mb-2">{{ __('Items') }}:</h4>
 <div class="space-y-2">
 @foreach($items as $item)
 <div class="flex justify-between text-sm">
 <span>{{ $item['description'] }} x {{ $item['quantity'] }}</span>
 <span>{{ $this->currency_symbol }}{{ number_format((float) $item['total'], 2) }}</span>
 </div>
 @endforeach
 </div>
 </div>

 <div class="border-t pt-4 space-y-2">
 <div class="flex justify-between">
 <span>{{ __('Subtotal') }}:</span>
 <span>{{ $this->currency_symbol }}{{ number_format((float) $this->totals['subtotal'], 2) }}</span>
 </div>
 <div class="flex justify-between">
 <span>{{ __('Tax') }}:</span>
 <span>{{ $this->currency_symbol }}{{ number_format((float) $this->totals['tax_total'], 2) }}</span>
 </div>
 <div class="flex justify-between">
 <span>{{ __('Discount') }}:</span>
 <span>-{{ $this->currency_symbol }}{{ number_format((float) $this->totals['discount'], 2) }}</span>
 </div>
 <div class="flex justify-between font-bold text-lg pt-2 border-t">
 <span>{{ __('Total') }}:</span>
 <span>{{ $this->currency_symbol }}{{ number_format((float) $this->totals['grand_total'], 2) }}</span>
 </div>
 </div>
 </div>
 </div>
 </div>
 @endif

 <!-- Navigation Buttons -->
 <div class="mt-6 flex justify-between">
 @if($step > 1)
 <button wire:click="previousStep"
 class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-page transition">
 ← {{ __('Previous') }}
 </button>
 @else
 <div></div>
 @endif

 @if($step < 4)
 <button wire:click="nextStep" class="px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition">
 {{ __('Next') }} →
 </button>
 @else
 <button wire:click="save" wire:loading.attr="disabled"
 class="inline-flex items-center px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50">
 <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
 viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor"
 d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
 </path>
 </svg>
 <span>{{ __('Create Estimate') }}</span>
 </button>
 @endif
 </div>
</div>