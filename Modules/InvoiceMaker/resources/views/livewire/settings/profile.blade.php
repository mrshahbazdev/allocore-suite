@php $title = __('Business Profile'); @endphp

<div>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-txmain">{{ __('Business Profile') }}</h2>
        <p class="text-txmain">{{ __('Manage your business information') }}</p>
    </div>

    <div class="">
        @if(session()->has('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        @if(session()->has('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form wire:submit="save" class="bg-card rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Business Name') }}</label>
                    <input type="text" wire:model="name"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="{{ __('Your Business Name') }}">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Email') }}</label>
                    <input type="email" wire:model="email"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="{{ __('business@example.com') }}">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Phone') }}</label>
                    <input type="text" wire:model="phone"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="{{ __('+1 (555) 123-4567') }}">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Tax Number / VAT / GST') }}</label>
                    <input type="text" wire:model="tax_number"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="{{ __('e.g. US123456789') }}">
                    @error('tax_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div wire:ignore>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Address') }}</label>
                    <textarea wire:model="address" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="{{ __('123 Business Street') }}&#10;{{ __('City, State ZIP') }}"></textarea>
                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-txmain mb-1">{{ __('Bank Details (Shown on Invoices)') }}</label>
                    <textarea wire:model="bank_details" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="{{ __('Bank Name: Example Bank') }}&#10;{{ __('Account No: 123456789') }}&#10;{{ __('Routing/Swift: EXMPUS33') }}"></textarea>
                    @error('bank_details') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-txmain mb-1">{{ __('IBAN (For EPC-QR Bank Transfer)') }}</label>
                        <input type="text" wire:model="iban"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="DE00 0000 0000 0000 0000 00">
                        @error('iban') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-txmain mb-1">{{ __('BIC') }}</label>
                        <input type="text" wire:model="bic"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="EXAMPPBBXXX">
                        @error('bic') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label
                        class="block text-sm font-medium text-txmain mb-1">{{ __('Default Payment Terms & Instructions') }}</label>
                    <textarea wire:model="payment_terms" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="{{ __('e.g. Please initiate wire transfer immediately.') }}"></textarea>
                    @error('payment_terms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Currency') }}</label>
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

                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Language') }}</label>
                    <select wire:model="language"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="en">English</option>
                        <option value="de">German (Deutsch)</option>
                        <option value="es">Spanish (Español)</option>
                        <option value="fr">French (Français)</option>
                        <option value="it">Italian (Italiano)</option>
                        <option value="pt">Portuguese (Português)</option>
                        <option value="ar">Arabic (العربية)</option>
                        <option value="zh">Chinese (中文)</option>
                        <option value="ja">Japanese (日本語)</option>
                        <option value="ru">Russian (Русский)</option>
                    </select>
                    @error('language') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Timezone') }}</label>
                    <select wire:model="timezone"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="UTC">UTC</option>
                        <option value="America/New_York">America/New_York</option>
                        <option value="America/Los_Angeles">America/Los_Angeles</option>
                        <option value="America/Chicago">America/Chicago</option>
                        <option value="Europe/London">Europe/London</option>
                        <option value="Europe/Paris">Europe/Paris</option>
                        <option value="Asia/Tokyo">Asia/Tokyo</option>
                        <option value="Asia/Singapore">Asia/Singapore</option>
                        <option value="Australia/Sydney">Australia/Sydney</option>
                    </select>
                    @error('timezone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Logo') }}</label>
                    <div class="flex items-center gap-4">
                        @if($business && $business->logo)
                            <div class="relative group">
                                <img src="{{ asset('storage/' . $business->logo) }}" alt="Logo"
                                    class="h-12 w-auto rounded border">
                                <button type="button" wire:click="removeLogo" wire:loading.attr="disabled"
                                    class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                        <input type="file" wire:model="logo"
                            class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                    @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="logo" class="mt-1 text-xs text-brand-600">{{ __('Uploading...') }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Invoice Number Prefix') }}</label>
                    <input type="text" wire:model="invoice_number_prefix"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="e.g. INV">
                    @error('invoice_number_prefix') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Next Invoice Number') }}</label>
                    <input type="number" wire:model="invoice_number_next"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="1">
                    @error('invoice_number_next') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Estimate Number Prefix') }}</label>
                    <input type="text" wire:model="estimate_number_prefix"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="e.g. EST">
                    @error('estimate_number_prefix') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Next Estimate Number') }}</label>
                    <input type="number" wire:model="estimate_number_next"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="1">
                    @error('estimate_number_next') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-txmain mb-1">{{ __('Booking Number Prefix (Expenses)') }}</label>
                    <input type="text" wire:model="booking_number_prefix"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="e.g. EXP">
                    @error('booking_number_prefix') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Next Booking Number') }}</label>
                    <input type="number" wire:model="booking_number_next"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="1">
                    @error('booking_number_next') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Bank Booking Account') }}</label>
                    <input type="text" wire:model="bank_booking_account"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="e.g. 1200">
                    @error('bank_booking_account') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-txmain mb-1">{{ __('Cash Booking Account') }}</label>
                    <input type="text" wire:model="cash_booking_account"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="e.g. 1000">
                    @error('cash_booking_account') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-8 border-t border-gray-200 pt-8">
                <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Automated Payment Reminders') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-sm font-semibold text-txmain">{{ __('Enable Automated Reminders') }}
                                </h4>
                                <p class="text-xs text-gray-500">
                                    {{ __('Automatically email clients when their invoices become overdue') }}
                                </p>
                            </div>
                            <button type="button"
                                wire:click="$set('enable_automated_reminders', {{ !$enable_automated_reminders ? 'true' : 'false' }})"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $enable_automated_reminders ? 'bg-brand-600' : 'bg-gray-200' }}">
                                <span
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-card shadow ring-0 transition duration-200 ease-in-out {{ $enable_automated_reminders ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>

                    @if($enable_automated_reminders)
                        <div x-transition>
                            <label
                                class="block text-sm font-medium text-txmain mb-1">{{ __('Send reminders every X days') }}</label>
                            <input type="number" wire:model="reminder_days_interval" min="1" max="365"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                                placeholder="7">
                            @error('reminder_days_interval') <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    <div>
                        <label
                            class="block text-sm font-medium text-txmain mb-1">{{ __('Late Fee Percentage (%)') }}</label>
                        <input type="number" wire:model="late_fee_percentage" min="0" max="100" step="0.01"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="0.00">
                        <p class="text-xs text-gray-500 mt-1">
                            {{ __('Applied monthly to overdue invoices. Set to 0 to disable.') }}</p>
                        @error('late_fee_percentage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-8 border-t border-gray-200 pt-8">
                <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('B2B Network Sync') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-sm font-semibold text-txmain">{{ __('Accept Network Invoices') }}
                                </h4>
                                <p class="text-xs text-gray-500">
                                    {{ __('Automatically record incoming platform invoices directly into your Expenses.') }}
                                </p>
                            </div>
                            <button type="button"
                                wire:click="$set('accept_network_invoices', {{ !$accept_network_invoices ? 'true' : 'false' }})"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $accept_network_invoices ? 'bg-brand-600' : 'bg-gray-200' }}">
                                <span
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-card shadow ring-0 transition duration-200 ease-in-out {{ $accept_network_invoices ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-8 border-t border-gray-200 pt-8">
                <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Email Settings (Optional SMTP)') }}</h3>
                <p class="text-sm text-gray-500 mb-6">
                    {{ __('Configure your own SMTP server to send emails from your own domain. If left empty, the system default will be used.') }}
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-txmain mb-1">{{ __('SMTP Host') }}</label>
                        <input type="text" wire:model="smtp_host"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="smtp.example.com">
                        @error('smtp_host') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-txmain mb-1">{{ __('SMTP Port') }}</label>
                        <input type="number" wire:model="smtp_port"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="587">
                        @error('smtp_port') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-txmain mb-1">{{ __('SMTP Username') }}</label>
                        <input type="text" wire:model="smtp_username"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        @error('smtp_username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-txmain mb-1">{{ __('SMTP Password') }}</label>
                        <input type="password" wire:model="smtp_password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        @error('smtp_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-txmain mb-1">{{ __('Encryption') }}</label>
                        <select wire:model="smtp_encryption"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            <option value="">{{ __('None') }}</option>
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                        </select>
                        @error('smtp_encryption') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-txmain mb-1">{{ __('From Email') }}</label>
                        <input type="email" wire:model="smtp_from_address"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="info@yourcompany.com">
                        @error('smtp_from_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-txmain mb-1">{{ __('From Name') }}</label>
                        <input type="text" wire:model="smtp_from_name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="Your Company Name">
                        @error('smtp_from_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-between pt-6">
                        <div>
                            <h4 class="text-sm font-semibold text-txmain">{{ __('Verify SSL Certificate') }}</h4>
                            <p class="text-xs text-gray-500">{{ __('Recommended. Disable if you have certificate mismatch issues.') }}</p>
                        </div>
                        <button type="button"
                            wire:click="$set('smtp_verify_ssl', {{ !$smtp_verify_ssl ? 'true' : 'false' }})"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 {{ $smtp_verify_ssl ? 'bg-brand-600' : 'bg-gray-200' }}">
                            <span
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-card shadow ring-0 transition duration-200 ease-in-out {{ $smtp_verify_ssl ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="flex items-end pb-1 gap-4">
                        <button type="button" wire:click="testSmtpConnection" wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-txmain bg-card hover:bg-page focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                            <svg wire:loading wire:target="testSmtpConnection"
                                class="animate-spin -ml-1 mr-3 h-5 w-5 text-brand-500"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ __('Test Connection') }}
                        </button>

                        @if(session()->has('message'))
                            <span x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="text-sm text-green-600 font-medium animate-pulse">
                                ✓ {{ session('message') }}
                            </span>
                        @endif

                        @if(session()->has('error'))
                            <span x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)" class="text-sm text-red-600 font-medium">
                                ✕ {{ session('error') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mb-8 border-t border-gray-200 pt-8">
                <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Online Payments') }}</h3>
                <div class="bg-page rounded-lg p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-base font-medium text-txmain">{{ __('Stripe Integration') }}</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ __('Accept credit cards, Apple Pay, and Google Pay directly on your invoices.') }}
                            </p>
                        </div>
                        @if(!$business)
                            <p class="text-sm text-gray-400 italic">
                                {{ __('Create your business profile first to connect Stripe.') }}
                            </p>
                        @elseif($stripe_onboarding_complete)
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ __('Connected') }}
                                </span>
                            </div>
                        @else
                            <button type="button" wire:click="connectStripe"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M13.976 9.15c-2.172-.806-3.356-1.143-3.356-2.076 0-.776.78-1.42 2.308-1.42 1.34 0 2.872.54 4.02 1.258l1.325-3.56A10.824 10.824 0 0012.784 2c-3.792 0-6.19 1.947-6.19 4.796 0 3.737 4.197 4.547 5.926 5.093 2.14.678 3.12 1.256 3.12 2.238 0 .86-.88 1.488-2.617 1.488-1.503 0-3.32-.61-4.706-1.487l-1.393 3.61c1.472.8 3.518 1.257 5.564 1.257 3.96 0 6.33-1.928 6.33-4.887 0-3.5-3.615-4.14-5.842-5.02z" />
                                </svg>
                                {{ __('Connect with Stripe') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center bg-brand-600 text-white py-2 px-6 rounded-lg hover:bg-brand-700 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ $business ? __('Save Changes') : __('Create Business Profile') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>