@php
    $layout = auth()->check() ? 'layouts.shell' : 'layouts.public';
    $title = __('Wissenssuche & Ergebnisse: ') . ($query ?: __('Übersicht'));
@endphp
@extends($layout)

@section('title', $title)

@section('content')
    <div class="mx-auto max-w-5xl py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">{{ __('Wissenssuche & Index') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Durchsuchen Sie FAQs, Praxisantworten, Fachartikel, Glossarbegriffe und Plattform-Inhalte.') }}</p>
        </div>

        <form method="GET" action="{{ route('search') }}" class="relative flex items-center shadow-sm rounded-2xl overflow-hidden border border-slate-300">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="search" name="q" value="{{ $query }}" placeholder="{{ __('Suchbegriff eingeben (z. B. Pricing, Vertrieb, Skalierung, Rechnungen)...') }}" class="w-full border-0 bg-white py-3.5 pl-11 pr-28 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-[#ff9200]">
            <button type="submit" class="absolute right-2 top-2 bottom-2 inline-flex items-center rounded-xl bg-[#ff9200] px-5 text-xs font-bold text-white shadow-xs hover:bg-orange-600 transition">
                {{ __('Suchen') }}
            </button>
        </form>

        @if ($query !== '')
            @if (empty($results))
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500 shadow-xs space-y-3">
                    <div class="text-3xl">🔍</div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('Keine Treffer gefunden für') }} „{{ $query }}“</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">{{ __('Versuchen Sie einen allgemeineren Begriff oder stöbern Sie direkt in unseren FAQs:') }}</p>
                    <div>
                        <a href="{{ route('faq.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">
                            💡 {{ __('Zum FAQ-Hub') }} &rarr;
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    @foreach ($results as $groupKey => $group)
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                    <span>
                                        @if($groupKey === 'faqs') 💡
                                        @elseif($groupKey === 'blog') 📰
                                        @elseif($groupKey === 'glossary') 📚
                                        @else 📁
                                        @endif
                                    </span>
                                    {{ $group['module'] }}
                                </h2>
                                <span class="text-xs font-semibold text-slate-400">
                                    {{ count($group['records']) }} {{ count($group['records']) === 1 ? __('Treffer') : __('Treffer') }}
                                </span>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach ($group['records'] as $record)
                                    <div class="py-3 flex flex-col justify-between gap-1 group">
                                        <div class="flex items-center justify-between gap-2">
                                            <a href="{{ $record['url'] ?? '#' }}" class="text-sm font-bold text-slate-900 group-hover:text-[#ff9200] transition">
                                                {{ $record['title'] }}
                                            </a>
                                            @if(!empty($record['category']))
                                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 uppercase">
                                                    {{ $record['category'] }}
                                                </span>
                                            @endif
                                        </div>
                                        @if(!empty($record['description']))
                                            <p class="text-xs text-slate-500 line-clamp-2">{{ $record['description'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endsection

