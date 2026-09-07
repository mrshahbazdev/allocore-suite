@php $title = __('How the Knowledge Library Works'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="mx-auto max-w-5xl space-y-6">
        <section class="rounded-3xl bg-slate-950 px-6 py-10 text-white shadow-sm sm:px-10">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#ffb34d]">{{ __('Product guide') }}</p>
            <h1 class="mt-3 max-w-3xl text-3xl font-bold sm:text-4xl">{{ __('From a shelf of books to useful organizational knowledge') }}</h1>
            <p class="mt-4 max-w-3xl text-base leading-7 text-slate-300">{{ __('The Knowledge Library helps your team understand what each book teaches, who should read it, and how it supports ongoing learning.') }}</p>
            <a href="{{ route('bookintelligence.books.create') }}" class="mt-6 inline-flex rounded-xl bg-[#ff9200] px-5 py-3 text-sm font-semibold text-white hover:bg-orange-600">{{ __('Add a book now') }}</a>
        </section>

        <section class="grid gap-5 md:grid-cols-3">
            @foreach ([
                [__('Library Setup'), __('Create main topics, subtopics, authors, and publishers that can be reused across all books.'), route('bookintelligence.setup.index')],
                [__('Book Library'), __('Store complete book metadata and classify each book by knowledge area, difficulty, and job role.'), route('bookintelligence.books.index')],
                [__('Reading Progress'), __('Each user builds a personal reading plan while the shared book catalog stays consistent for the team.'), route('bookintelligence.books.index', ['reading_status' => 'reading'])],
            ] as [$heading, $copy, $link])
                <a href="{{ $link }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-orange-200 hover:shadow-md">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-sm font-bold text-[#ff9200]">{{ $loop->iteration }}</span>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">{{ $heading }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $copy }}</p>
                    <span class="mt-4 inline-flex text-sm font-semibold text-[#0094af]">{{ __('Open') }} →</span>
                </a>
            @endforeach
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('A practical workflow') }}</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-900">{{ __('What to do when adding a new book') }}</h2>
            <ol class="mt-6 space-y-6">
                @foreach ([
                    [__('Capture the identity'), __('Add the title, author, publisher, ISBN, language, year, and page count.')],
                    [__('Explain the value'), __('Write a short description focused on the business problems and learning outcomes the book supports.')],
                    [__('Classify the knowledge'), __('Choose a main topic, select subtopics, set difficulty, and list the job roles that benefit most.')],
                    [__('Connect access'), __('Optionally add a cover image and affiliate purchase link so employees can quickly find the book.')],
                    [__('Start learning'), __('Add the book to your personal reading plan and keep progress and notes updated.')],
                ] as [$heading, $copy])
                    <li class="grid gap-3 sm:grid-cols-[44px_1fr]">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">{{ $loop->iteration }}</span>
                        <div>
                            <h3 class="font-bold text-slate-900">{{ $heading }}</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500">{{ $copy }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="rounded-2xl border border-orange-200 bg-orange-50 p-6 sm:p-8">
            <h2 class="text-xl font-bold text-orange-900">{{ __('What comes next?') }}</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-orange-800">{{ __('This structured library is the foundation for future AI summaries, extracted frameworks, question mapping, knowledge-gap detection, and book recommendations. Better metadata now means better AI results later.') }}</p>
        </section>
    </div>
@endsection
