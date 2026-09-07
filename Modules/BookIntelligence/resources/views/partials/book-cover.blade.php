@php($size = $size ?? 'medium')
@php($classes = $size === 'large' ? 'h-72 w-48' : 'h-40 w-28')

@if ($book->cover_url)
    <img src="{{ $book->cover_url }}" alt="{{ __('Cover of :title', ['title' => $book->title]) }}" class="{{ $classes }} rounded-xl object-cover shadow-md ring-1 ring-slate-900/10">
@else
    <div class="{{ $classes }} flex shrink-0 flex-col justify-between rounded-xl bg-gradient-to-br from-slate-800 to-slate-950 p-4 text-white shadow-md ring-1 ring-slate-900/10">
        <svg class="h-7 w-7 text-[#ff9200]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-19.5 0A2.25 2.25 0 004.5 15h15a2.25 2.25 0 002.25-2.25m-19.5 0v6A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25v-6M6.75 9.75V5.25A2.25 2.25 0 019 3h6a2.25 2.25 0 012.25 2.25v4.5" />
        </svg>
        <p class="line-clamp-4 text-sm font-bold leading-snug">{{ $book->title }}</p>
    </div>
@endif
