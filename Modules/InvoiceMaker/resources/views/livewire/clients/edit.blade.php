@php $title = __('Edit Client'); @endphp

<div>
 <div class="mb-8">
 <h2 class="text-2xl font-bold text-txmain">{{ __('Edit Client') }}</h2>
 <p class="text-txmain">{{ __('Update client information') }}</p>
 </div>

 <div class="max-w-2xl">
 <form wire:submit="save" class="bg-card rounded-lg shadow p-6">
 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Contact Name') }} *</label>
 <input type="text" wire:model="name"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Email') }}</label>
 <input type="email" wire:model="email"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Phone') }}</label>
 <input type="text" wire:model="phone"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Company Name') }}</label>
 <input type="text" wire:model="company_name"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('company_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Address') }}</label>
 <textarea wire:model="address" rows="3"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"></textarea>
 @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Tax Number') }}</label>
 <input type="text" wire:model="tax_number"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('tax_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Currency Override') }}</label>
 <select wire:model="currency"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="USD">{{ __('USD - US Dollar') }}</option>
 <option value="EUR">{{ __('EUR - Euro') }}</option>
 <option value="GBP">{{ __('GBP - British Pound') }}</option>
 <option value="CAD">{{ __('CAD - Canadian Dollar') }}</option>
 <option value="AUD">{{ __('AUD - Australian Dollar') }}</option>
 <option value="JPY">{{ __('JPY - Japanese Yen') }}</option>
 <option value="PKR">{{ __('PKR - Pakistani Rupee') }}</option>
 <option value="INR">{{ __('INR - Indian Rupee') }}</option>
 <option value="AED">{{ __('AED - UAE Dirham') }}</option>
 </select>
 @error('currency') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Language Preference') }}</label>
 <select wire:model="language"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="en">{{ __('English') }}</option>
 <option value="es">{{ __('Spanish') }} (Español)</option>
 <option value="fr">{{ __('French') }} (Français)</option>
 <option value="de">{{ __('German') }} (Deutsch)</option>
 <option value="it">{{ __('Italian') }} (Italiano)</option>
 <option value="pt">{{ __('Portuguese') }} (Português)</option>
 <option value="ar">{{ __('Arabic') }} (العربية)</option>
 <option value="zh">{{ __('Chinese') }} (中文)</option>
 <option value="ja">{{ __('Japanese') }} (日本語)</option>
 <option value="ru">{{ __('Russian') }} (Русский)</option>
 </select>
 @error('language') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 <p class="mt-2 text-xs text-gray-500">
 {{ __('This language will be used for PDFs and portal experiences.') }}
 </p>
 </div>

 <div class="mb-6 border-t pt-6">
 <h3 class="text-lg font-medium text-txmain mb-4">{{ __('Default Email Settings (Optional)') }}</h3>
 <p class="text-sm text-gray-500 mb-4">
 {{ __('Predefine a subject and body pattern for invoices sent to this client. You can use available placeholders like:') }}
 <code class="text-xs bg-page px-1 rounded">{invoice_number}</code>, <code
 class="text-xs bg-page px-1 rounded">{client_name}</code>, <code
 class="text-xs bg-page px-1 rounded">{business_name}</code>, <code
 class="text-xs bg-page px-1 rounded">{amount_due}</code>, <code
 class="text-xs bg-page px-1 rounded">{due_date}</code>, <code
 class="text-xs bg-page px-1 rounded">{public_link}</code>.
 </p>

 <div class="mb-4">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Custom Subject') }}</label>
 <input type="text" wire:model="email_subject"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('Invoice {invoice_number} from {business_name}') }}">
 @error('email_subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-4">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Custom Body Template') }}</label>
 <textarea wire:model="email_template" rows="5"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('Hello {client_name},\n\nHere is your new invoice {invoice_number}...') }}"></textarea>
 @error('email_template') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Notes') }}</label>
 <textarea wire:model="notes" rows="2"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"></textarea>
 @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="flex justify-between">
 <a href="{{ route('invoicemaker.clients.index') }}"
 class="text-txmain hover:text-txmain py-2 px-4 rounded-lg border border-gray-300">{{ __('Cancel') }}</a>
 <button type="submit" wire:loading.attr="disabled"
 class="inline-flex items-center bg-brand-600 text-white py-2 px-6 rounded-lg hover:bg-brand-700 transition duration-200 disabled:opacity-50">
 <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
 viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
 </circle>
 <path class="opacity-75" fill="currentColor"
 d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
 </path>
 </svg>
 <span>{{ __('Update Client') }}</span>
 </button>
 </div>
 </form>
 </div>
</div>