@php $title = __('Knowledge Library'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6" data-no-navigate>
        <section class="overflow-hidden rounded-3xl bg-slate-950 shadow-sm">
            <div class="grid gap-8 px-6 py-8 sm:px-8 lg:grid-cols-[1.4fr_1fr] lg:items-center lg:px-10 lg:py-10">
                <div>
                    <span class="inline-flex rounded-full bg-[#ff9200]/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#ffb34d]">
                        {{ __('BookIntelligence') }}
                    </span>
                    <h1 class="mt-4 max-w-2xl text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        {{ __('Build knowledge people can actually find and use.') }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-base leading-7 text-slate-300">
                        {{ __('Organize books by topic and role, turn them into practical AI knowledge, and track learning progress in one place.') }}
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('bookintelligence.books.create') }}" class="rounded-xl bg-[#ff9200] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600">
                            {{ __('Add your first book') }}
                        </a>
                        <a href="{{ route('bookintelligence.guide') }}" class="rounded-xl border border-slate-700 bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                            {{ __('See how it works') }}
                        </a>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Your next best action') }}</p>
                    @php($nextStep = collect($setup)->firstWhere('complete', false))
                    @if ($nextStep)
                        <h2 class="mt-2 text-xl font-bold text-white">{{ $nextStep['label'] }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-300">{{ $nextStep['description'] }}</p>
                        <a href="{{ $nextStep['route'] }}" class="mt-5 inline-flex items-center text-sm font-semibold text-[#ffb34d] hover:text-white">
                            {{ $nextStep['action'] }}
                            <svg class="ml-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @else
                        <h2 class="mt-2 text-xl font-bold text-white">{{ __('Explore your knowledge library') }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-300">{{ __('Your library is ready. Continue adding books and keep your reading progress current.') }}</p>
                        <a href="{{ route('bookintelligence.books.index') }}" class="mt-5 inline-flex items-center text-sm font-semibold text-[#ffb34d] hover:text-white">{{ __('Open Book Library') }}</a>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Knowledge library statistics') }}">
            @foreach ([
                [__('Books'), $stats['books'], __('Available knowledge sources')],
                [__('AI-ready books'), $stats['analyzed'], __('Summaries and actions available')],
                [__('Topics'), $stats['topics'], __('Knowledge areas organized')],
                [__('Books read'), $stats['read'], __('Your completed reading')],
            ] as [$label, $value, $description])
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($value) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
                </div>
            @endforeach
        </section>

        <div class="grid gap-6 xl:grid-cols-[1fr_1.7fr]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Quick start') }}</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('Set up in four steps') }}</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ collect($setup)->where('complete', true)->count() }}/{{ count($setup) }} {{ __('done') }}
                    </span>
                </div>
                <ol class="mt-6 space-y-5">
                    @foreach ($setup as $step)
                        <li class="flex gap-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $step['complete'] ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' }}">
                                @if ($step['complete'])
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </span>
                            <div class="min-w-0">
                                <h3 class="font-semibold text-slate-900">{{ $step['label'] }}</h3>
                                <p class="mt-1 text-sm leading-5 text-slate-500">{{ $step['description'] }}</p>
                                <a href="{{ $step['route'] }}" class="mt-2 inline-flex text-sm font-semibold text-[#0094af] hover:underline">{{ $step['action'] }}</a>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Recently added') }}</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('Latest books') }}</h2>
                    </div>
                    <a href="{{ route('bookintelligence.books.index') }}" class="text-sm font-semibold text-[#ff9200] hover:text-orange-600">{{ __('View all books') }}</a>
                </div>

                @if ($books->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                        <h3 class="font-semibold text-slate-900">{{ __('Your library is empty') }}</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">{{ __('Start with one high-value book. You can enrich its topics, role relevance, and reading plan as you go.') }}</p>
                        <a href="{{ route('bookintelligence.books.create') }}" class="mt-5 inline-flex rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">{{ __('Add a book') }}</a>
                    </div>
                @else
                    <div class="mt-5 divide-y divide-slate-100">
                        @foreach ($books as $book)
                            <a href="{{ route('bookintelligence.books.show', $book) }}" class="flex items-center gap-4 py-4 first:pt-0 last:pb-0 hover:bg-slate-50">
                                <div class="flex h-12 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[#ff9200]">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.966 8.966 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25A8.966 8.966 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.966 8.966 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-slate-900">{{ $book->title }}</p>
                                    <p class="truncate text-sm text-slate-500">{{ $book->author?->name ?? __('Author not set') }} · {{ $book->mainTopic?->name ?? __('Uncategorized') }}</p>
                                </div>
                                <span class="hidden rounded-full px-2.5 py-1 text-xs font-semibold sm:inline-flex {{ $book->analysis?->isCompleted() ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $book->analysis?->isCompleted() ? __('AI ready') : __('Needs analysis') }}
                                </span>
                                @include('bookintelligence::partials.reading-status', ['status' => $book->readingStatus()])
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    [__('1. Organize'), __('Add trustworthy book metadata and classify knowledge by topic and role.')],
                    [__('2. Discover'), __('Search and filter the library to find the right book for a goal or problem.')],
                    [__('3. Analyze'), __('Generate concise summaries, core lessons, reusable frameworks, and next actions.')],
                    [__('4. Learn'), __('Build a reading plan, update progress, and apply useful insights from each book.')],
                ] as [$heading, $copy])
                    <div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0094af]/10 text-sm font-bold text-[#0094af]">{{ $loop->iteration }}</span>
                        <h2 class="mt-4 font-bold text-slate-900">{{ $heading }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $copy }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
