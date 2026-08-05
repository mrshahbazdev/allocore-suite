@php $title = __('Edit Template'); @endphp

<div>
 <div class="mb-8">
 <h2 class="text-2xl font-bold text-txmain">{{ __('Edit Template') }}</h2>
 <p class="text-txmain">{{ __('Customize invoice appearance') }}</p>
 </div>

 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
 <div class="bg-card rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Template Settings') }}</h3>

 <form wire:submit="save">
 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Template Name') }}</label>
 <input type="text" wire:model.live.debounce.300ms="name"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Primary Color') }}</label>
 <div class="flex gap-2">
 <input type="color" wire:model.live="primary_color"
 class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
 <input type="text" wire:model.live.debounce.300ms="primary_color"
 class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('#3B82F6') }}">
 </div>
 @error('primary_color') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Font Family') }}</label>
 <select wire:model.live="font_family"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="sans">{{ __('Sans Serif (Helvetica, Arial)') }}</option>
 <option value="serif">{{ __('Serif (Times, Georgia)') }}</option>
 <option value="mono">{{ __('Monospace (Courier, Consolas)') }}</option>
 </select>
 @error('font_family') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Logo Position') }}</label>
 <select wire:model.live="logo_position"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="left">{{ __('Left') }}</option>
 <option value="center">{{ __('Center') }}</option>
 <option value="right">{{ __('Right') }}</option>
 </select>
 @error('logo_position') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Header Style') }}</label>
 <select wire:model.live="header_style"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 <option value="default">{{ __('Default') }}</option>
 <option value="bold">{{ __('Bold & Highlighted') }}</option>
 <option value="minimal">{{ __('Minimalist') }}</option>
 </select>
 @error('header_style') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-2">{{ __('Display Options') }}</label>
 <div class="flex items-center gap-6">
 <label class="flex items-center">
 <input type="checkbox" wire:model.live="show_tax"
 class="rounded border-gray-300 text-brand-600 shadow-sm focus:border-brand-300 focus:ring focus:ring-brand-200 focus:ring-opacity-50">
 <span class="ml-2 text-sm text-txmain">{{ __('Show Tax Column') }}</span>
 </label>
 <label class="flex items-center">
 <input type="checkbox" wire:model.live="show_discount"
 class="rounded border-gray-300 text-brand-600 shadow-sm focus:border-brand-300 focus:ring focus:ring-brand-200 focus:ring-opacity-50">
 <span class="ml-2 text-sm text-txmain">{{ __('Show Discount') }}</span>
 </label>
 <label class="flex items-center">
 <input type="checkbox" wire:model.live="enable_qr"
 class="rounded border-gray-300 text-brand-600 shadow-sm focus:border-brand-300 focus:ring focus:ring-brand-200 focus:ring-opacity-50">
 <span class="ml-2 text-sm text-txmain">{{ __('Enable QR Code') }}</span>
 </label>
 </div>
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Payment Terms') }}</label>
 <textarea wire:model.live.debounce.500ms="payment_terms" rows="2"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('e.g. Net 30, Due on Receipt...') }}"></textarea>
 @error('payment_terms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Footer Message') }}</label>
 <textarea wire:model.live.debounce.500ms="footer_message" rows="2"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
 placeholder="{{ __('Thank you for your business!') }}"></textarea>
 @error('footer_message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Signature Upload') }}</label>
 @if($signature_path)
 <div class="mb-4">
 <img src="{{ asset('storage/' . $signature_path) }}" alt="Signature"
 class="h-16 w-auto border rounded p-1">
 </div>
 @endif
 <input type="file" wire:model="signature"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
 @error('signature') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>

 <div class="flex justify-end">
 <button type="submit"
 class="bg-brand-600 text-white py-2 px-6 rounded-lg hover:bg-brand-700 transition duration-200">
 {{ __('Save Template') }}
 </button>
 </div>
 </form>
 </div>

 <div class="bg-card rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-txmain mb-4">{{ __('Preview') }}</h3>
 <div class="border rounded-lg p-4 font-{{ $font_family }} {{ $header_style === 'bold' ? 'bg-page' : '' }}"
 style="border-color: {{ $primary_color }};">
 <div
 class="flex justify-between items-start mb-6 {{ $logo_position === 'right' ? 'flex-row-reverse text-right' : ($logo_position === 'center' ? 'flex-col items-center text-center' : '') }}">
 <div>
 <div class="text-2xl font-bold mb-2" style="color: {{ $primary_color }};">
 {{ __('Your Business') }}</div>
 <p class="text-sm text-txmain">business@example.com</p>
 <p class="text-sm text-txmain">+1 (555) 123-4567</p>
 </div>
 <div class="text-right">
 <div class="text-xl font-bold text-txmain">{{ __('INVOICE') }}</div>
 <p class="text-sm text-txmain">INV-2024-0001</p>
 <p class="text-sm text-txmain">Jan 15, 2024</p>
 </div>
 </div>

 <div class="grid grid-cols-2 gap-4 mb-6">
 <div>
 <p class="text-xs font-bold text-gray-500 uppercase">{{ __('Bill To') }}</p>
 <p class="font-medium text-txmain">John Doe</p>
 <p class="text-sm text-txmain">Acme Corporation</p>
 <p class="text-sm text-txmain">john@example.com</p>
 </div>
 <div>
 <p class="text-xs font-bold text-gray-500 uppercase">{{ __('Ship To') }}</p>
 <p class="font-medium text-txmain">Acme Corporation</p>
 <p class="text-sm text-txmain">123 Business Street</p>
 <p class="text-sm text-txmain">San Francisco, CA 94102</p>
 </div>
 </div>

 <table class="w-full text-sm mb-4">
 <thead>
 <tr class="border-b-2" style="border-color: {{ $primary_color }};">
 <th class="text-left py-2 font-bold text-txmain uppercase text-xs">{{ __('Description') }}
 </th>
 <th class="text-right py-2 font-bold text-txmain uppercase text-xs">{{ __('Qty') }}</th>
 <th class="text-right py-2 font-bold text-txmain uppercase text-xs">{{ __('Price') }}</th>
 <th class="text-right py-2 font-bold text-txmain uppercase text-xs">{{ __('Total') }}</th>
 </tr>
 </thead>
 <tbody>
 <tr class="border-b border-gray-200">
 <td class="py-2">Web Development Package</td>
 <td class="py-2 text-right">1</td>
 <td class="py-2 text-right">$2,500.00</td>
 <td class="py-2 text-right">$2,500.00</td>
 </tr>
 <tr class="border-b border-gray-200">
 <td class="py-2">Monthly Maintenance</td>
 <td class="py-2 text-right">3</td>
 <td class="py-2 text-right">$200.00</td>
 <td class="py-2 text-right">$600.00</td>
 </tr>
 </tbody>
 </table>

 <div class="flex justify-end">
 <div class="w-48">
 <div class="flex justify-between py-1 text-sm">
 <span class="text-txmain">{{ __('Subtotal') }}:</span>
 <span>$3,100.00</span>
 </div>
 <div class="flex justify-between py-1 text-sm">
 <span class="text-txmain">{{ __('Tax') }}:</span>
 <span>$0.00</span>
 </div>
 <div class="flex justify-between py-2 border-t font-bold"
 style="border-color: {{ $primary_color }};">
 <span>{{ __('Total') }}:</span>
 <span style="color: {{ $primary_color }};">$3,100.00</span>
 </div>
 </div>
 </div>
 <div class="flex justify-between items-start mt-8">
 <div class="w-1/2">
 @if($payment_terms)
 <div class="mb-4">
 <p class="text-xs font-bold text-gray-500 uppercase">{{ __('Payment Terms') }}</p>
 <p class="text-xs text-txmain mt-1 whitespace-pre-line">{{ $payment_terms }}</p>
 </div>
 @endif
 @if($signature_path)
 <div class="mt-6 border-b border-gray-300 inline-block px-10 pb-2 mb-2">
 <img src="{{ asset('storage/' . $signature_path) }}" alt="Signature" class="h-12 w-auto">
 </div>
 <p class="text-xs text-gray-500">{{ __('Authorized Signature') }}</p>
 @endif
 </div>
 <div>
 @if($enable_qr)
 <div class="border p-2 bg-page flex items-center justify-center w-24 h-24 rounded">
 {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->generate('https://example.com/invoice/preview') !!}
 </div>
 @endif
 </div>
 </div>

 @if($footer_message)
 <div class="mt-8 text-center text-xs text-gray-500 py-4 border-t"
 style="border-color: {{ $primary_color }}33;">
 {{ $footer_message }}
 </div>
 @endif
 </div>
 </div>
 </div>
</div>