@php $title = __('Knowledge Search Engine'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="mx-auto max-w-5xl space-y-8" data-no-navigate>
        <!-- Header -->
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 4 — Knowledge Search Engine') }}</p>
            <h1 class="mt-1 text-3xl font-extrabold text-slate-900 sm:text-4xl">{{ __('Ask Anything Across Your Book Library') }}</h1>
            <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-500">
                {{ __('Ask complex natural language questions. The AI searches through all books, pinpoints exact methodologies, provides a direct answer, and recommends the right books.') }}
            </p>
        </div>

        <!-- Search input box -->
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-xl sm:p-6">
            <form method="GET" action="{{ route('bookintelligence.search.index') }}" class="relative">
                <div class="flex items-center gap-3">
                    <span class="text-2xl text-slate-400 pl-2">🔍</span>
                    <input type="search" name="q" value="{{ $query }}" autofocus placeholder="{{ __('Ask a question (e.g. "How can I reduce DSO?", "How to scale sales team?")...') }}" class="w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0 sm:text-lg">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-6 py-3.5 text-sm font-bold text-white shadow-md transition hover:from-orange-600 hover:to-orange-700">
                        <span>{{ __('Search') }}</span> &rarr;
                    </button>
                </div>
            </form>

            <!-- Sample questions pills -->
            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 text-xs">
                <span class="font-semibold text-slate-400">{{ __('Try asking:') }}</span>
                @foreach ($sampleQuestions as $sample)
                    <a href="{{ route('bookintelligence.search.index', ['q' => $sample]) }}" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-slate-600 transition hover:bg-orange-50 hover:text-orange-700">
                        {{ $sample }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Search Results Section -->
        @if (filled($query) && $searchResult)
            @if ($searchResult['has_results'] && $searchResult['primary_book'])
                <!-- AI Direct Answer Card -->
                <div class="overflow-hidden rounded-3xl border border-cyan-100 bg-gradient-to-br from-white via-cyan-50/20 to-cyan-100/30 p-6 shadow-sm sm:p-8">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#0094af]">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#0094af] text-xs text-white">✨</span>
                        {{ __('Synthesized Knowledge Answer') }}
                    </div>

                    <div class="prose prose-slate mt-4 max-w-none text-slate-800 leading-relaxed">
                        {!! nl2br(e($searchResult['answer'])) !!}
                    </div>

                    <!-- Primary Source Book Spotlight -->
                    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                            @if ($searchResult['primary_book']->cover_url)
                                <img src="{{ $searchResult['primary_book']->cover_url }}" alt="{{ $searchResult['primary_book']->title }}" class="h-36 w-24 flex-shrink-0 rounded-xl object-cover shadow">
                            @else
                                <div class="flex h-36 w-24 flex-shrink-0 items-center justify-center rounded-xl bg-slate-100 text-3xl text-slate-400 shadow">
                                    📖
                                </div>
                            @endif

                            <div class="flex-1 space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-xs font-semibold uppercase text-emerald-600">{{ __('Primary Source Book') }}</p>
                                        <h3 class="text-lg font-bold text-slate-900">{{ $searchResult['primary_book']->title }}</h3>
                                        <p class="text-xs text-slate-500">{{ __('By') }} {{ $searchResult['primary_book']->author?->name ?? 'Unknown Author' }}</p>
                                    </div>
                                    <a href="{{ route('bookintelligence.books.show', $searchResult['primary_book']->id) }}" class="inline-flex items-center gap-1 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                                        {{ __('View Full Summary & Intelligence') }} &rarr;
                                    </a>
                                </div>

                                @if ($searchResult['primary_book']->analysis?->short_summary)
                                    <p class="text-xs leading-relaxed text-slate-600">
                                        <strong>{{ __('1–2 Min Summary:') }}</strong> {{ $searchResult['primary_book']->analysis->short_summary }}
                                    </p>
                                @elseif ($searchResult['primary_book']->description)
                                    <p class="text-xs leading-relaxed text-slate-600">
                                        {{ Str::limit($searchResult['primary_book']->description, 200) }}
                                    </p>
                                @endif

                                @if ($searchResult['primary_book']->affiliate_link)
                                    <div class="pt-2">
                                        <a href="{{ $searchResult['primary_book']->affiliate_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-900 hover:bg-amber-100">
                                            🛒 {{ __('Get Book on Amazon') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Companion Recommended Books -->
                    @if ($searchResult['companion_books']->isNotEmpty())
                        <div class="mt-8 border-t border-cyan-100/60 pt-6">
                            <h4 class="text-sm font-bold text-slate-900">{{ __('Additional Recommended Reading on this Topic:') }}</h4>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                @foreach ($searchResult['companion_books']->take(4) as $comp)
                                    <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="text-2xl">📚</div>
                                        <div class="flex-1 min-w-0">
                                            <h5 class="text-sm font-bold text-slate-900 truncate">{{ $comp->title }}</h5>
                                            <p class="text-xs text-slate-500">{{ $comp->author?->name }}</p>
                                            <a href="{{ route('bookintelligence.books.show', $comp->id) }}" class="mt-2 inline-block text-xs font-semibold text-[#0094af] hover:underline">
                                                {{ __('Explore Book') }} &rarr;
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <!-- Knowledge Gap Alert (Module 5) -->
                <div class="rounded-3xl border border-amber-200 bg-amber-50/70 p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-3xl">
                        ⚠️
                    </div>
                    <span class="mt-3 inline-flex rounded-full bg-amber-200 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-900">
                        {{ __('Knowledge Gap Detected') }}
                    </span>
                    <h3 class="mt-3 text-xl font-bold text-slate-900">{{ __('No Direct Answer Found in Current Library') }}</h3>
                    <p class="mx-auto mt-2 max-w-lg text-sm text-slate-600">
                        {{ __('We could not find sufficient literature in your library for: ') }} <em>"{{ $query }}"</em>.
                    </p>
                    <p class="mt-2 text-xs text-amber-800">
                        {{ __('This question has been automatically recorded into the Knowledge Gap Queue for admin review and AI book acquisition recommendations.') }}
                    </p>
                    <div class="mt-6 flex items-center justify-center gap-3">
                        <a href="{{ route('bookintelligence.gaps.index') }}" class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-semibold text-white hover:bg-slate-800">
                            {{ __('View Knowledge Gap Dashboard & Recommended Acquisitions') }} &rarr;
                        </a>
                    </div>
                </div>
            @endif
        @endif

        <!-- Recent Searches Section -->
        @if ($recentSearches->isNotEmpty() && blank($query))
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900">{{ __('Recent Knowledge Searches') }}</h3>
                <div class="mt-3 divide-y divide-slate-100">
                    @foreach ($recentSearches as $recent)
                        <div class="flex items-center justify-between py-2.5 text-xs">
                            <a href="{{ route('bookintelligence.search.index', ['q' => $recent->query]) }}" class="font-medium text-slate-700 hover:text-[#0094af]">
                                {{ $recent->query }}
                            </a>
                            <div class="flex items-center gap-2">
                                @if ($recent->has_results)
                                    <span class="rounded bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700">{{ __('Answered') }}</span>
                                @else
                                    <span class="rounded bg-amber-50 px-2 py-0.5 font-medium text-amber-700">{{ __('Gap Recorded') }}</span>
                                @endif
                                <span class="text-slate-400">{{ $recent->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
