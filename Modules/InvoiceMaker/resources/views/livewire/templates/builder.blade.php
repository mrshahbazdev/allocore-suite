<div>
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Edit Template') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Customize invoice appearance') }}</p>
        </div>
        <a href="{{ route('invoicemaker.templates.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Back to templates') }}</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Template Settings') }}</h3>

            <form wire:submit="save" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Template Name') }}</label>
                    <input type="text" wire:model.live.debounce.300ms="name" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Primary Color') }}</label>
                    <div class="mt-1 flex gap-2">
                        <input type="color" wire:model.live="primary_color" class="h-10 w-12 cursor-pointer rounded-lg border border-slate-300">
                        <input type="text" wire:model.live.debounce.300ms="primary_color" class="flex-1 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="#4f46e5">
                    </div>
                    @error('primary_color') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Font Family') }}</label>
                        <select wire:model.live="font_family" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="sans">{{ __('Sans Serif') }}</option>
                            <option value="serif">{{ __('Serif') }}</option>
                            <option value="mono">{{ __('Monospace') }}</option>
                        </select>
                        @error('font_family') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Logo Position') }}</label>
                        <select wire:model.live="logo_position" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="left">{{ __('Left') }}</option>
                            <option value="center">{{ __('Center') }}</option>
                            <option value="right">{{ __('Right') }}</option>
                        </select>
                        @error('logo_position') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Header Style') }}</label>
                    <select wire:model.live="header_style" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="default">{{ __('Default') }}</option>
                        <option value="bold">{{ __('Bold & Highlighted') }}</option>
                        <option value="minimal">{{ __('Minimalist') }}</option>
                    </select>
                    @error('header_style') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Display Options') }}</label>
                    <div class="flex flex-wrap items-center gap-6">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model.live="show_tax" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Show Tax Column') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model.live="show_discount" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Show Discount') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model.live="enable_qr" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Enable QR Code') }}
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Payment Terms') }}</label>
                    <textarea wire:model.live.debounce.500ms="payment_terms" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('e.g. Net 30, Due on Receipt...') }}"></textarea>
                    @error('payment_terms') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Footer Message') }}</label>
                    <textarea wire:model.live.debounce.500ms="footer_message" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Thank you for your business!') }}"></textarea>
                    @error('footer_message') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Signature Upload') }}</label>
                    @if($signature_path)
                        <div class="mb-3 flex items-center gap-3">
                            <img src="{{ asset('storage/' . $signature_path) }}" alt="Signature" class="h-16 rounded border border-slate-200 bg-white p-1">
                            <button type="button" wire:click="removeSignature" class="text-sm text-rose-600 hover:underline">{{ __('Remove') }}</button>
                        </div>
                    @endif
                    <input type="file" wire:model="signature" class="mt-1 block w-full rounded-lg border-slate-300 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-600 hover:file:bg-indigo-100">
                    @error('signature') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        {{ __('Save Template') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Preview') }}</h3>
            <div class="overflow-x-auto rounded-xl border p-4 font-{{ $font_family }}" style="border-color: {{ $primary_color }};">
                <div class="mb-6 flex items-start justify-between {{ $logo_position === 'right' ? 'flex-row-reverse text-right' : ($logo_position === 'center' ? 'flex-col items-center text-center' : '') }}">
                    <div>
                        <div class="mb-1 text-2xl font-bold" style="color: {{ $primary_color }};">{{ __('Your Business') }}</div>
                        <p class="text-sm text-slate-600">business@example.com</p>
                        <p class="text-sm text-slate-600">+1 (555) 123-4567</p>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-bold text-slate-900">{{ __('INVOICE') }}</div>
                        <p class="text-sm text-slate-600">INV-2024-0001</p>
                        <p class="text-sm text-slate-600">Jan 15, 2024</p>
                    </div>
                </div>

                <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-500">{{ __('Bill To') }}</p>
                        <p class="font-medium text-slate-900">John Doe</p>
                        <p class="text-slate-600">Acme Corporation</p>
                        <p class="text-slate-600">john@example.com</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-500">{{ __('Ship To') }}</p>
                        <p class="font-medium text-slate-900">Acme Corporation</p>
                        <p class="text-slate-600">123 Business Street</p>
                        <p class="text-slate-600">San Francisco, CA 94102</p>
                    </div>
                </div>

                <table class="mb-4 w-full text-sm">
                    <thead>
                        <tr class="border-b-2" style="border-color: {{ $primary_color }};">
                            <th class="py-2 text-left text-xs font-bold uppercase text-slate-900">{{ __('Description') }}</th>
                            <th class="py-2 text-right text-xs font-bold uppercase text-slate-900">{{ __('Qty') }}</th>
                            <th class="py-2 text-right text-xs font-bold uppercase text-slate-900">{{ __('Price') }}</th>
                            <th class="py-2 text-right text-xs font-bold uppercase text-slate-900">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-slate-200">
                            <td class="py-2 text-slate-700">Web Development Package</td>
                            <td class="py-2 text-right text-slate-700">1</td>
                            <td class="py-2 text-right text-slate-700">$2,500.00</td>
                            <td class="py-2 text-right text-slate-700">$2,500.00</td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-2 text-slate-700">Monthly Maintenance</td>
                            <td class="py-2 text-right text-slate-700">3</td>
                            <td class="py-2 text-right text-slate-700">$200.00</td>
                            <td class="py-2 text-right text-slate-700">$600.00</td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex justify-end">
                    <div class="w-48 text-sm">
                        <div class="flex justify-between py-1 text-slate-700">
                            <span>{{ __('Subtotal') }}:</span>
                            <span>$3,100.00</span>
                        </div>
                        <div class="flex justify-between py-1 text-slate-700">
                            <span>{{ __('Tax') }}:</span>
                            <span>$0.00</span>
                        </div>
                        <div class="flex justify-between border-t py-2 text-base font-bold" style="border-color: {{ $primary_color }};">
                            <span>{{ __('Total') }}:</span>
                            <span style="color: {{ $primary_color }};">$3,100.00</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex items-start justify-between text-sm">
                    <div class="w-1/2">
                        @if($payment_terms)
                            <div class="mb-4">
                                <p class="text-xs font-bold uppercase text-slate-500">{{ __('Payment Terms') }}</p>
                                <p class="mt-1 whitespace-pre-line text-slate-600">{{ $payment_terms }}</p>
                            </div>
                        @endif
                        @if($signature_path)
                            <div class="mt-4 inline-block border-b border-slate-300 px-10 pb-2">
                                <img src="{{ asset('storage/' . $signature_path) }}" alt="Signature" class="h-12 w-auto">
                            </div>
                            <p class="text-xs text-slate-500">{{ __('Authorized Signature') }}</p>
                        @endif
                    </div>
                    <div>
                        @if($enable_qr)
                            <div class="flex h-24 w-24 items-center justify-center rounded border border-slate-200 bg-slate-50">
                                <span class="text-[10px] text-slate-400">QR</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($footer_message)
                    <div class="mt-8 border-t py-4 text-center text-xs text-slate-500" style="border-color: {{ $primary_color }}33;">
                        {{ $footer_message }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
