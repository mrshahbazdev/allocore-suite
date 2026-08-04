<div>
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-txmain">{{ __('Email Templates') }}</h2>
            <p class="text-txmain">{{ __('Manage custom email templates for your invoices and reminders.') }}</p>
        </div>
        <button wire:click="create"
            class="inline-flex items-center bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-700 transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            {{ __('New Template') }}
        </button>
    </div>

    <!-- Instructions / Available Variables -->
    <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-5">
        <h3 class="text-lg font-semibold text-blue-800 mb-2">{{ __('Available Variables') }}</h3>
        <p class="text-sm text-blue-700 mb-4">
            {{ __('You can use the following variables in your Subject or Body. They will be replaced dynamically when the email is sent.') }}
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <div class="bg-white px-3 py-2 rounded border border-blue-100 text-sm"><code
                    class="text-brand-600 font-bold">[client_name]</code> <span class="text-gray-500">- Client's
                    name</span></div>
            <div class="bg-white px-3 py-2 rounded border border-blue-100 text-sm"><code
                    class="text-brand-600 font-bold">[business_name]</code> <span class="text-gray-500">- Your business
                    name</span></div>
            <div class="bg-white px-3 py-2 rounded border border-blue-100 text-sm"><code
                    class="text-brand-600 font-bold">[invoice_number]</code> <span class="text-gray-500">- Invoice
                    number</span></div>
            <div class="bg-white px-3 py-2 rounded border border-blue-100 text-sm"><code
                    class="text-brand-600 font-bold">[amount_due]</code> <span class="text-gray-500">- Amount due</span>
            </div>
            <div class="bg-white px-3 py-2 rounded border border-blue-100 text-sm"><code
                    class="text-brand-600 font-bold">[total_amount]</code> <span class="text-gray-500">- Total
                    amount</span></div>
            <div class="bg-white px-3 py-2 rounded border border-blue-100 text-sm"><code
                    class="text-brand-600 font-bold">[due_date]</code> <span class="text-gray-500">- Due date</span>
            </div>
            <div class="bg-white px-3 py-2 rounded border border-blue-100 text-sm"><code
                    class="text-brand-600 font-bold">[invoice_link]</code> <span class="text-gray-500">- Public invoice
                    URL</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @forelse($templates as $template)
            <div class="bg-card rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col md:flex-row">
                <div class="p-6 flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-bold text-txmain">{{ $template->name }}</h3>
                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-medium {{ $template->type === 'invoice' ? 'bg-blue-100 text-blue-800' : ($template->type === 'reminder' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($template->type) }}
                        </span>
                        @if($template->is_default)
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                {{ __('Default') }}
                            </span>
                        @endif
                    </div>
                    <p class="text-sm font-medium text-gray-600 mb-1"><span
                            class="text-gray-400">{{ __('Subject:') }}</span> {{ $template->subject }}</p>
                    <p class="text-sm text-gray-500 line-clamp-2 mt-2">{{ strip_tags($template->body) }}</p>
                </div>
                <div
                    class="bg-gray-50/50 p-6 md:w-48 border-t md:border-t-0 md:border-l border-gray-200 flex flex-row md:flex-col justify-center gap-3">
                    <button wire:click="edit({{ $template->id }})"
                        class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-brand-700 bg-brand-50 border border-brand-200 rounded-lg hover:bg-brand-100 transition-colors">
                        {{ __('Edit') }}
                    </button>
                    @if(!$template->is_default)
                        <button wire:click="setAsDefault({{ $template->id }})"
                            class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            {{ __('Set Default') }}
                        </button>
                        <button wire:click="delete({{ $template->id }})"
                            wire:confirm="{{ __('Are you sure you want to delete this template?') }}"
                            class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                            {{ __('Delete') }}
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-card rounded-xl border border-gray-200 border-dashed">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                    </path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-txmain">{{ __('No email templates') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Get started by creating a new template.') }}</p>
                <div class="mt-6">
                    <button wire:click="create"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ __('New Template') }}
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-card px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-5">
                                <h3 class="text-lg leading-6 font-bold text-txmain" id="modal-title">
                                    {{ $template_id ? __('Edit Email Template') : __('Create Email Template') }}
                                </h3>
                                <button type="button" wire:click="$set('showModal', false)"
                                    class="text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-txmain mb-1">{{ __('Template Name') }}</label>
                                        <input type="text" wire:model.defer="name"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm"
                                            placeholder="e.g. Standard Invoice">
                                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-txmain mb-1">{{ __('Template Type') }}</label>
                                        <select wire:model.defer="type"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                                            <option value="invoice">{{ __('Invoice') }}</option>
                                            <option value="reminder">{{ __('Reminder') }}</option>
                                            <option value="receipt">{{ __('Receipt') }}</option>
                                        </select>
                                        @error('type') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-txmain mb-1">{{ __('Email Subject') }}</label>
                                    <input type="text" wire:model.defer="subject"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm"
                                        placeholder="e.g. New Invoice from [business_name] - [invoice_number]">
                                    @error('subject') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-txmain mb-1">{{ __('Email Body (HTML allowed)') }}</label>
                                    <textarea wire:model.defer="body" rows="8"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm"
                                        placeholder="<p>Hi [client_name],</p><br><p>Please find attached...</p>"></textarea>
                                    @error('body') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="flex items-center mt-4">
                                    <input id="is_default" type="checkbox" wire:model.defer="is_default"
                                        class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded">
                                    <label for="is_default" class="ml-2 block text-sm text-txmain">
                                        {{ __('Set as default template for this type') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-600 text-base font-medium text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                {{ __('Save Template') }}
                            </button>
                            <button type="button" wire:click="$set('showModal', false)"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>