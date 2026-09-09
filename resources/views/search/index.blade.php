@php
    $layout = auth()->check() ? 'layouts.shell' : 'layouts.public';
    $title = $query ? __('Suchergebnisse für: ') . e($query) : __('Globale Suche & Wissensindex');
@endphp
@extends($layout)

@section('title', $title)

@section('content')
<div class="bg-slate-50 min-h-screen py-10">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Search Header Card -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-10 shadow-sm space-y-6">
            <div class="text-center sm:text-left">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-orange-800">
                    🔍 {{ __('Allocore Wissens- & Plattformsuche') }}
                </span>
                <h1 class="mt-3 text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    {{ __('Was suchen Sie?') }}
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    {{ __('Finden Sie Antworten in FAQs, Fachartikeln, Glossarbegriffen, Rechnungen, Kontakten und Plattformmodulen.') }}
                </p>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('search') }}" class="relative flex items-center shadow-md rounded-2xl overflow-hidden border-2 border-slate-200 focus-within:border-[#ff9200] transition bg-white">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       name="q" 
                       value="{{ $query }}" 
                       autocomplete="off"
                       placeholder="{{ __('Suchbegriff eingeben (z. B. Pricing, Vertrieb, Leadership, Skalierung, Rechnungen)...') }}" 
                       style="color: #0f172a !important; background-color: #ffffff !important; font-size: 1.05rem !important;"
                       class="w-full border-0 bg-white py-4 pl-12 pr-32 text-slate-900 font-medium placeholder:text-slate-400 focus:outline-none focus:ring-0">
                
                @if($query !== '')
                    <a href="{{ route('search') }}" class="absolute right-28 top-3.5 bottom-3.5 px-2 flex items-center text-slate-400 hover:text-slate-700 text-lg font-bold" title="{{ __('Suche leeren') }}">
                        &times;
                    </a>
                @endif

                <button type="submit" class="absolute right-2 top-2 bottom-2 inline-flex items-center rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-6 text-xs sm:text-sm font-bold text-white shadow-sm hover:from-orange-600 hover:to-orange-700 transition cursor-pointer">
                    {{ __('Suchen') }}
                </button>
            </form>

            <!-- Quick Suggestions -->
            <div class="flex flex-wrap items-center gap-2 pt-1 text-xs text-slate-500">
                <span class="font-bold text-slate-700">{{ __('Häufige Suchen:') }}</span>
                @foreach(['Pricing', 'Vertrieb', 'Leadership', 'OKRs', 'Liquidität', 'Skalierung', 'Rechnungen'] as $suggest)
                    <a href="{{ route('search', ['q' => $suggest]) }}" class="rounded-lg bg-slate-100 px-2.5 py-1 font-medium text-slate-700 hover:bg-orange-50 hover:text-orange-700 transition">
                        {{ $suggest }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Search Results Section -->
        @if ($query !== '')
            @if (empty($results))
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500 shadow-sm space-y-4">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-50 text-3xl text-orange-600">
                        🔍
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Keine Treffer für „:query“ gefunden', ['query' => $query]) }}</h3>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
                        {{ __('Versuchen Sie einen allgemeineren Suchbegriff oder stöbern Sie direkt in unserem FAQ- und Wissensbereich.') }}
                    </p>
                    <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('faq.index') }}" class="rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:from-orange-600 hover:to-orange-700 transition">
                            💡 {{ __('Alle FAQs durchstöbern') }} &rarr;
                        </a>
                        <a href="{{ route('glossary.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                            📚 {{ __('Zum Glossar') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-1">
                        <p class="text-xs sm:text-sm font-semibold text-slate-700">
                            {{ __('Ergebnisse für') }} <strong class="text-slate-900">„{{ $query }}“</strong>
                        </p>
                        <a href="{{ route('faq.index') }}" class="text-xs font-bold text-[#0094af] hover:underline">
                            💡 {{ __('Zur FAQ-Übersicht') }} &rarr;
                        </a>
                    </div>

                    @foreach ($results as $groupKey => $group)
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                    <span class="text-lg">{{ $group['icon'] ?? '📁' }}</span>
                                    <span>{{ $group['module'] }}</span>
                                </h2>
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                                    {{ count($group['records']) }} {{ count($group['records']) === 1 ? __('Treffer') : __('Treffer') }}
                                </span>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach ($group['records'] as $record)
                                    <div class="py-3.5 flex flex-col justify-between gap-1.5 group">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <a href="{{ $record['url'] ?? '#' }}" class="text-base font-bold text-slate-900 group-hover:text-[#ff9200] transition">
                                                {{ $record['title'] }}
                                            </a>
                                            @if(!empty($record['category']))
                                                <span class="rounded-md bg-blue-50 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 uppercase">
                                                    {{ $record['category'] }}
                                                </span>
                                            @endif
                                        </div>
                                        @if(!empty($record['description']))
                                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed line-clamp-2">
                                                {{ $record['description'] }}
                                            </p>
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
</div>
@endsection
