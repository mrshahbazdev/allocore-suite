@php $title = __('Brand Colors'); @endphp

<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#ff9200] text-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597l-5.814 3.876a15.996 15.996 0 00-4.648 4.764m3.42 3.42l-3.42-3.42" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#ff9200]">{{ __('Dashboard') }}</a>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    <a href="{{ route('admin.settings.index') }}" class="hover:text-[#ff9200]">{{ __('Site Settings') }}</a>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    <span class="text-slate-900">{{ __('Brand Colors') }}</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ __('Brand Colors') }}</h1>
                <p class="mt-1 max-w-xl text-sm text-slate-500">{{ __('Manage the brand primary and secondary colors used across the app.') }}</p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="card">
        <h2 class="card-title">{{ __('Colors') }}</h2>

        <div class="form-grid">
            <div>
                <label for="primary_hex" class="form-label">{{ __('Primary color') }}</label>
                <div class="flex items-center gap-3">
                    <input type="color" wire:model.live="primary" id="primary_picker" class="h-10 w-14 shrink-0 cursor-pointer rounded-lg border border-slate-300" aria-label="{{ __('Primary color') }}">
                    <input type="text" wire:model.live.debounce.500ms="primary" id="primary_hex" maxlength="7" class="form-control" placeholder="#ff9200">
                </div>
                @error('primary')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="secondary_hex" class="form-label">{{ __('Secondary color') }}</label>
                <div class="flex items-center gap-3">
                    <input type="color" wire:model.live="secondary" id="secondary_picker" class="h-10 w-14 shrink-0 cursor-pointer rounded-lg border border-slate-300" aria-label="{{ __('Secondary color') }}">
                    <input type="text" wire:model.live.debounce.500ms="secondary" id="secondary_hex" maxlength="7" class="form-control" placeholder="#0094af">
                </div>
                @error('secondary')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Live Preview') }}</h3>
            <div class="flex flex-wrap items-center gap-4">
                <button type="button" class="btn" style="background-color: {{ $primary }}; border-color: {{ $primary }}; color: #fff;">
                    {{ __('Primary Button') }}
                </button>

                <button type="button" class="btn border" style="border-color: {{ $secondary }}; color: {{ $secondary }};">
                    {{ __('Secondary Button') }}
                </button>

                <div class="w-56 rounded-xl border border-slate-200 bg-white p-4 shadow-sm" style="border-top: 4px solid {{ $primary }};">
                    <span class="badge" style="background-color: {{ $primary }}; color: #fff;">{{ __('New') }}</span>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ __('Sample Card') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('This is how cards use the brand colors.') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-end gap-3">
            <button type="button" wire:click="resetDefaults" class="btn btn-secondary">{{ __('Reset to defaults') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>
    </form>
</div>
