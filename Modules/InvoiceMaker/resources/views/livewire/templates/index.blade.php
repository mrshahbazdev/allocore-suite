<div>
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Invoice templates') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Control PDF colors, headings, terms, and footer content.') }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-3">
            @foreach($templates as $template)
                <button wire:click="edit({{ $template->id }})" class="w-full rounded-xl border p-4 text-left shadow-sm {{ $selectedId === $template->id ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 bg-white' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-slate-900">{{ $template->name }}</span>
                        @if($template->is_default)
                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs text-emerald-700">{{ __('Default') }}</span>
                        @endif
                    </div>
                    <div class="mt-3 h-2 rounded-full" style="background: {{ $template->primary_color }}"></div>
                    <div class="mt-3 flex items-center justify-end gap-3">
                        <a href="{{ route('invoicemaker.templates.edit', $template) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ __('Open builder') }}</a>
                    </div>
                </button>
            @endforeach
            <button wire:click="$set('selectedId', null)" class="w-full rounded-xl border border-dashed border-slate-300 p-4 text-sm font-medium text-indigo-600">{{ __('Create template') }}</button>
        </div>

        <form wire:submit="save" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="grid gap-4 sm:grid-cols-2">
                <label>
                    <span class="text-sm text-slate-600">{{ __('Name') }}</span>
                    <input wire:model="name" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label>
                    <span class="text-sm text-slate-600">{{ __('Primary color') }}</span>
                    <input wire:model="primary_color" type="color" class="mt-1 h-10 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label>
                    <span class="text-sm text-slate-600">{{ __('Font family') }}</span>
                    <select wire:model="font_family" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="sans">{{ __('Sans Serif') }}</option>
                        <option value="serif">{{ __('Serif') }}</option>
                        <option value="mono">{{ __('Monospace') }}</option>
                    </select>
                </label>
                <label>
                    <span class="text-sm text-slate-600">{{ __('Header style') }}</span>
                    <select wire:model="header_style" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="default">{{ __('Default') }}</option>
                        <option value="bold">{{ __('Bold') }}</option>
                        <option value="minimal">{{ __('Minimalist') }}</option>
                    </select>
                </label>
                <div class="sm:col-span-2 flex flex-wrap items-center gap-5">
                    <label class="flex items-center gap-2 text-sm">
                        <input wire:model="show_tax" type="checkbox" class="rounded border-slate-300 text-indigo-600"> {{ __('Show tax') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input wire:model="show_discount" type="checkbox" class="rounded border-slate-300 text-indigo-600"> {{ __('Show discount') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input wire:model="enable_qr" type="checkbox" class="rounded border-slate-300 text-indigo-600"> {{ __('Enable QR') }}
                    </label>
                </div>
            </div>
            <label class="block">
                <span class="text-sm text-slate-600">{{ __('Payment terms') }}</span>
                <textarea wire:model="payment_terms" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </label>
            <label class="block">
                <span class="text-sm text-slate-600">{{ __('Footer message') }}</span>
                <textarea wire:model="footer_message" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </label>
            <div class="flex justify-end gap-3">
                @if($selectedId)
                    <a href="{{ route('invoicemaker.templates.edit', $selectedId) }}" class="rounded-lg border border-indigo-200 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50">{{ __('Full builder') }}</a>
                    <button type="button" wire:click="makeDefault({{ $selectedId }})" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium focus:border-indigo-500 focus:ring-indigo-500">{{ __('Make default') }}</button>
                @endif
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save template') }}</button>
            </div>
        </form>
    </div>
</div>
