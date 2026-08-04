@php $title = __('Record Expense'); @endphp

<div>
 <div class="mb-8 flex items-center justify-between">
 <div>
 <h2 class="text-2xl font-bold text-txmain">{{ __('Record Expense') }}</h2>
 <p class="text-txmain">{{ __('Enter your expense details and upload a receipt') }}</p>
 </div>
 <a href="{{ route('invoicemaker.expenses.index') }}" class="text-txmain hover:text-txmain">{{ __('Back to List') }}</a>
 </div>

 <div class="max-w-3xl mx-auto">
 <div class="bg-card rounded-lg shadow p-6">
 <form wire:submit="save">
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Date') }}</label>
 <input type="date" wire:model="date"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Payment Source') }}</label>
 <select wire:model="source"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="cash">{{ __('Cash (Kasse)') }}</option>
 <option value="bank">{{ __('Bank') }}</option>
 </select>
 @error('source') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Category') }}</label>
 <select wire:model.live="category_id"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="">{{ __('Select Category') }}</option>
 @foreach($categories as $cat)
 <option value="{{ $cat->id }}">{{ $cat->name }}
 {{ $cat->booking_account ? "({$cat->booking_account})" : '' }}
 </option>
 @endforeach
 </select>
 @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label
 class="block text-sm font-medium text-txmain mb-1">{{ __('Link to Invoice (Optional)') }}</label>
 <select wire:model="invoice_id"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="">{{ __('No Link') }}</option>
 @foreach($invoices as $inv)
 <option value="{{ $inv->id }}">[{{ $inv->invoice_number }}] {{ $inv->client->name }}
 </option>
 @endforeach
 </select>
 @error('invoice_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
 <div>
 <label
 class="block text-sm font-medium text-txmain mb-1">{{ __('Link to Client (Optional)') }}</label>
 <select wire:model="client_id"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="">{{ __('No Link') }}</option>
 @foreach($clients as $client)
 <option value="{{ $client->id }}">{{ $client->name }}</option>
 @endforeach
 </select>
 @error('client_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label
 class="block text-sm font-medium text-txmain mb-1">{{ __('Link to Product (Optional)') }}</label>
 <select wire:model="product_id"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="">{{ __('No Link') }}</option>
 @foreach($products as $product)
 <option value="{{ $product->id }}">{{ $product->name }}</option>
 @endforeach
 </select>
 @error('product_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>

 @if($posting_rule)
 <div class="mb-6 p-4 bg-brand-50 border-l-4 border-brand-400 rounded-r-lg">
 <div class="flex">
 <div class="flex-shrink-0">
 <svg class="h-5 w-5 text-brand-400" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd"
 d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
 clip-rule="evenodd" />
 </svg>
 </div>
 <div class="ml-3">
 <h3 class="text-sm font-bold text-brand-800 uppercase tracking-wider">
 {{ __('Posting Rule') }}
 </h3>
 <p class="text-sm text-brand-700 mt-1 italic">{{ $posting_rule }}</p>
 </div>
 </div>
 </div>
 @endif

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
 <div>
 <label
 class="block text-sm font-medium text-txmain mb-1">{{ __('Partner / Company') }}</label>
 <input type="text" wire:model="partner_name"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('Apple, Microsoft, etc.') }}">
 @error('partner_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label
 class="block text-sm font-medium text-txmain mb-1">{{ __('External Reference / Invoice #') }}</label>
 <input type="text" wire:model="reference_number"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('RE-12345') }}">
 @error('reference_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Amount') }}</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <span class="text-gray-500">{{ $profile->currency_symbol }}</span>
 </div>
 <input type="number" step="0.01" wire:model="amount"
 class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="0.00">
 </div>
 @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Description') }}</label>
 <input type="text" wire:model="description"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('What was this expense for?') }}">
 @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>

 <div class="mb-6">
 <label
 class="block text-sm font-medium text-txmain mb-1">{{ __('Receipt Image (Optional)') }}</label>
 <div
 class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
 <div class="space-y-1 text-center">
 @if ($receipt)
 <div class="mb-4">
 @if (in_array(strtolower($receipt->extension()), ['png', 'jpg', 'jpeg', 'gif', 'webp']))
 <img src="{{ $receipt->temporaryUrl() }}"
 class="mx-auto h-32 w-auto rounded-lg shadow-sm">
 @else
 <div class="flex items-center justify-center p-4">
 <svg class="h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd"
 d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
 clip-rule="evenodd" />
 </svg>
 <span
 class="ml-2 text-sm text-txmain">{{ $receipt->getClientOriginalName() }}</span>
 </div>
 @endif
 </div>
 <button type="button" wire:click="scanReceipt" wire:loading.attr="disabled"
 class="mt-2 text-xs font-semibold text-brand-600 hover:text-brand-800 flex items-center justify-center w-full bg-brand-50 py-2 rounded-md border border-brand-100 transition disabled:opacity-50">
 <svg wire:loading wire:target="scanReceipt"
 class="animate-spin -ml-1 mr-1 h-3 w-3 text-brand-600" fill="none"
 viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
 stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor"
 d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
 </path>
 </svg>
 <span wire:loading.remove wire:target="scanReceipt" class="mr-1">✨</span>
 <span wire:loading.remove
 wire:target="scanReceipt">{{ __('Scan Receipt with AI') }}</span>
 <span wire:loading wire:target="scanReceipt">{{ __('Analyzing Receipt...') }}</span>
 </button>
 @else
 <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
 viewBox="0 0 48 48" aria-hidden="true">
 <path
 d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
 </svg>
 @endif
 <div class="flex text-sm text-txmain">
 <label for="file-upload"
 class="relative cursor-pointer bg-card rounded-md font-medium text-brand-600 hover:text-brand-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-brand-500">
 <span>{{ __('Upload a file') }}</span>
 <input id="file-upload" wire:model="receipt" type="file" class="sr-only">
 </label>
 <p class="pl-1">{{ __('or drag and drop') }}</p>
 </div>
 <p class="text-xs text-gray-500">
 PNG, JPG, GIF, PDF {{ __('up to 2MB') }}
 </p>
 </div>
 </div>
 @error('receipt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="flex justify-end gap-3 mt-8">
 <a href="{{ route('invoicemaker.expenses.index') }}"
 class="px-6 py-2 border border-gray-300 rounded-lg text-txmain hover:bg-page transition">
 {{ __('Cancel') }}
 </a>
 <button type="submit" wire:loading.attr="disabled"
 class="inline-flex items-center px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 shadow-sm transition disabled:opacity-50">
 <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
 fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
 </circle>
 <path class="opacity-75" fill="currentColor"
 d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
 </path>
 </svg>
 <span>{{ __('Save Expense') }}</span>
 </button>
 </div>
 </form>
 </div>
 </div>
</div>