@php($current = app()->getLocale())

<div class="relative">
    <select
        onchange="window.location.href = this.value"
        class="appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs font-medium text-slate-700 hover:border-slate-300 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        aria-label="{{ __('Select language') }}"
    >
        @foreach (config('app.available_locales', ['en']) as $locale)
            <option value="{{ route('language', ['locale' => $locale]) }}" {{ $current === $locale ? 'selected' : '' }}>
                {{ config('app.locale_names.'.$locale, strtoupper($locale)) }}
            </option>
        @endforeach
    </select>
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
    </div>
</div>
