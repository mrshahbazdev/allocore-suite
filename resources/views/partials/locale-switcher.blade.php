@php($current = app()->getLocale())

<div class="flex items-center rounded-lg border border-slate-200 bg-white p-1 text-xs font-medium">
    @foreach (config('app.available_locales', ['en', 'de']) as $locale)
        <a href="{{ route('language', ['locale' => $locale]) }}" class="rounded-md px-3 py-1.5 {{ $current === $locale ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
            {{ strtoupper($locale) }}
        </a>
    @endforeach
</div>
