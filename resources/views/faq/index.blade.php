@php
    $title = __('Häufig gestellte Fragen (FAQ) & Wissensdatenbank');
    $metaDescription = __('Finden Sie Antworten auf häufige Fragen zu Skalierung, Vertrieb, Pricing, Leadership und Unternehmenswachstum.');
@endphp
@extends('layouts.public')

@section('title', $title)
@section('meta_description', $metaDescription)

@section('content')
<div class="bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 py-16 text-white sm:py-24">
    <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#ff9200]/20 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-[#ff9200] border border-[#ff9200]/30">
            💡 {{ __('Allocore Knowledge & FAQ Hub') }}
        </span>
        <h1 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
            {{ __('Wie können wir Ihnen helfen?') }}
        </h1>
        <p class="mx-auto mt-4 max-w-2xl text-base text-slate-300 sm:text-lg">
            {{ __('Durchsuchen Sie praxiserprobte Antworten, strategische Leitfäden und Lösungen aus über Hunderten von Fachbüchern und Best Practices.') }}
        </p>

        <!-- Search Bar -->
        <div class="mx-auto mt-8 max-w-2xl">
            <form method="GET" action="{{ route('faq.index') }}" class="relative flex items-center shadow-2xl rounded-2xl overflow-hidden border border-slate-200 bg-white">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       name="q" 
                       value="{{ $search }}" 
                       autocomplete="off"
                       placeholder="{{ __('Stichwort oder Frage eingeben (z. B. Pricing, OKRs, Vertrieb, Liquidität)...') }}" 
                       style="color: #0f172a !important; background-color: #ffffff !important; font-size: 1.05rem !important;"
                       class="w-full border-0 bg-white py-4.5 pl-12 pr-32 text-slate-900 font-medium placeholder:text-slate-400 focus:outline-none focus:ring-0">
                
                @if($search !== '')
                    <a href="{{ route('faq.index', ['category' => $selectedCategory]) }}" class="absolute right-28 top-3.5 bottom-3.5 px-2 flex items-center text-slate-400 hover:text-slate-700 text-lg font-bold" title="{{ __('Suche leeren') }}">
                        &times;
                    </a>
                @endif

                @if($selectedCategory !== 'all')
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                @endif
                <button type="submit" class="absolute right-2 top-2 bottom-2 inline-flex items-center rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-6 text-sm font-bold text-white shadow-sm hover:from-orange-600 hover:to-orange-700 transition cursor-pointer">
                    {{ __('Suchen') }}
                </button>
            </form>
        </div>
    </div>
</div>

<div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8" x-data="{ openFaq: null, showAskModal: false }">
    @if (session('success'))
        <div class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 shadow-sm flex items-center gap-3">
            <span class="text-xl">✅</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Category Filter Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-6 mb-8">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('faq.index', ['category' => 'all', 'q' => $search]) }}" class="rounded-full px-4 py-2 text-xs font-bold transition {{ $selectedCategory === 'all' || empty($selectedCategory) ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100' }}">
                {{ __('Alle Themen') }} ({{ $totalCount }})
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('faq.index', ['category' => $cat, 'q' => $search]) }}" class="rounded-full px-4 py-2 text-xs font-bold transition {{ $selectedCategory === $cat ? 'bg-[#ff9200] text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <button type="button" 
                @click.stop="showAskModal = true" 
                onclick="window.openFaqModal && window.openFaqModal()"
                class="inline-flex items-center gap-2 rounded-xl bg-[#0094af] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#007b91] transition cursor-pointer">
            ✍️ {{ __('Eigene Frage stellen') }}
        </button>
    </div>

    <!-- Active Search Filter Badge -->
    @if ($search !== '')
        <div class="mb-6 flex items-center justify-between rounded-xl bg-orange-50 border border-orange-200 px-4 py-3 text-xs text-orange-900">
            <span>{{ __('Suchergebnisse für:') }} <strong>„{{ $search }}“</strong> ({{ $faqs->total() }} {{ __('Gefunden') }})</span>
            <a href="{{ route('faq.index', ['category' => $selectedCategory]) }}" class="font-bold text-orange-700 underline hover:text-orange-900">
                &times; {{ __('Suche zurücksetzen') }}
            </a>
        </div>
    @endif

    <!-- FAQ Accordion List -->
    @if ($faqs->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-xs">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-50 text-3xl text-orange-600">
                🔍
            </div>
            <h3 class="mt-4 text-lg font-bold text-slate-900">{{ __('Keine passenden Fragen gefunden') }}</h3>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                {{ __('Haben Sie eine spezifische Frage, die hier noch nicht beantwortet wird? Reichen Sie Ihre Frage direkt ein:') }}
            </p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <a href="{{ route('faq.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                    {{ __('Alle Fragen anzeigen') }}
                </a>
                <button type="button" 
                        @click.stop="showAskModal = true" 
                        onclick="window.openFaqModal && window.openFaqModal()"
                        class="rounded-xl bg-[#ff9200] px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-orange-600 cursor-pointer">
                    ✍️ {{ __('Jetzt Frage stellen') }}
                </button>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($faqs as $faq)
                <div class="rounded-2xl border border-slate-200 bg-white shadow-xs transition hover:border-slate-300 overflow-hidden">
                    <button type="button" @click="openFaq = (openFaq === {{ $faq->id }} ? null : {{ $faq->id }})" class="flex w-full items-center justify-between p-5 text-left cursor-pointer">
                        <div class="flex items-center gap-3 pr-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-sm font-bold text-[#ff9200]">
                                ?
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $faq->question }}</h3>
                                @if ($faq->category)
                                    <span class="mt-1 inline-block rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-600">
                                        {{ $faq->category }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="shrink-0 text-slate-400">
                            <svg class="h-5 w-5 transform transition-transform duration-200" :class="openFaq === {{ $faq->id }} ? 'rotate-180 text-[#ff9200]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div x-show="openFaq === {{ $faq->id }}" x-cloak class="border-t border-slate-100 bg-slate-50/50 p-6 space-y-4">
                        <!-- Answer Excerpt -->
                        <div class="text-sm text-slate-700 leading-relaxed space-y-2">
                            <p class="font-medium text-slate-800">{{ $faq->answer_excerpt ?: $faq->problem_statement }}</p>
                        </div>

                        <!-- Details & Problem Statement -->
                        @if ($faq->problem_statement && $faq->answer_excerpt)
                            <div class="rounded-xl bg-white p-4 border border-slate-200 text-xs text-slate-600 space-y-1.5">
                                <span class="font-bold text-slate-900">🎯 {{ __('Hintergrund & Problemstellung:') }}</span>
                                <p>{{ $faq->problem_statement }}</p>
                            </div>
                        @endif

                        <!-- When to trigger / When relevant -->
                        @if ($faq->when_to_read_trigger)
                            <div class="text-xs text-slate-600 flex items-center gap-2">
                                <span class="font-bold text-amber-700">⚡ {{ __('Wann besonders relevant?') }}</span>
                                <span>{{ $faq->when_to_read_trigger }}</span>
                            </div>
                        @endif

                        <!-- Target Audience -->
                        @if (!empty($faq->target_audience))
                            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                <span class="text-[11px] font-bold uppercase text-slate-400 mr-1">{{ __('Zielgruppe:') }}</span>
                                @foreach ((array) $faq->target_audience as $aud)
                                    <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">
                                        {{ $aud }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Source Book Reference -->
                        @if ($faq->book)
                            <div class="mt-3 rounded-xl border border-slate-200 bg-white p-3 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-lg">📖</span>
                                    <div>
                                        <span class="text-slate-500">{{ __('Ausführliche Analyse & Strategie in:') }}</span>
                                        <strong class="block text-slate-900 font-bold">{{ $faq->book->title }}</strong>
                                    </div>
                                </div>
                                @if ($faq->book->author)
                                    <span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-700">
                                        {{ $faq->book->author->name }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $faqs->links() }}
        </div>
    @endif

    <!-- Ask Question Modal -->
    <div id="ask-modal"
         x-show="showAskModal" 
         x-cloak 
         @keydown.escape.window="showAskModal = false; window.closeFaqModal && window.closeFaqModal();"
         @click.self="showAskModal = false; window.closeFaqModal && window.closeFaqModal();"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div @click.stop class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Haben Sie eine Frage?') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Stellen Sie Ihre Frage direkt an unsere Wissensdatenbank.') }}</p>
                </div>
                <button type="button" 
                        @click.stop="showAskModal = false" 
                        onclick="window.closeFaqModal && window.closeFaqModal()"
                        class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer p-1">&times;</button>
            </div>

            <form method="POST" action="{{ route('faq.ask') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Ihr Name *') }}</label>
                        <input type="text" name="name" required placeholder="Max Mustermann" class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Ihre E-Mail-Adresse *') }}</label>
                        <input type="email" name="email" required placeholder="max@unternehmen.de" class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">{{ __('Themenbereich / Kategorie') }}</label>
                    <select name="category" class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="Allgemein">{{ __('Allgemein') }}</option>
                        <option value="Vertrieb & Sales">{{ __('Vertrieb & Sales') }}</option>
                        <option value="Pricing & Monetarisierung">{{ __('Pricing & Monetarisierung') }}</option>
                        <option value="Leadership & Führung">{{ __('Leadership & Führung') }}</option>
                        <option value="Finanzen & Liquidität">{{ __('Finanzen & Liquidität') }}</option>
                        <option value="Strategie & Skalierung">{{ __('Strategie & Skalierung') }}</option>
                        <option value="Marketing & SEO">{{ __('Marketing & SEO') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">{{ __('Ihre Frage *') }}</label>
                    <input type="text" name="question" required placeholder="z. B. Wie gestalte ich die Preisstaffelung für Enterprise-Kunden?" class="mt-1 w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-[#ff9200] focus:ring-[#ff9200]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">{{ __('Zusätzliche Details oder Kontext (Optional)') }}</label>
                    <textarea name="problem_details" rows="3" placeholder="Beschreiben Sie kurz Ihre aktuelle Herausforderung..." class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-[#ff9200] focus:ring-[#ff9200]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" 
                            @click.stop="showAskModal = false" 
                            onclick="window.closeFaqModal && window.closeFaqModal()"
                            class="cursor-pointer rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                        {{ __('Abbrechen') }}
                    </button>
                    <button type="submit" class="cursor-pointer rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:from-orange-600 hover:to-orange-700 transition">
                        🚀 {{ __('Frage absenden') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.openFaqModal = function() {
        var m = document.getElementById('ask-modal');
        if (m) {
            m.style.display = 'flex';
            m.removeAttribute('x-cloak');
        }
    };
    window.closeFaqModal = function() {
        var m = document.getElementById('ask-modal');
        if (m) {
            m.style.display = 'none';
        }
    };
</script>
@endsection
