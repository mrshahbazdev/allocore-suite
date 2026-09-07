@php $title = __('Book Library'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6" data-no-navigate>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Knowledge Library') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('Book Library') }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">{{ __('Find books by topic, difficulty, role relevance, or your reading status.') }}</p>
            </div>
            <a href="{{ route('bookintelligence.books.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#ff9200] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                {{ __('Add book') }}
            </a>
        </div>

        <form method="GET" action="{{ route('bookintelligence.books.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2">
                    <label for="q" class="sr-only">{{ __('Search books') }}</label>
                    <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search title, author, ISBN...') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                </div>
                <div>
                    <label for="topic" class="sr-only">{{ __('Topic') }}</label>
                    <select id="topic" name="topic" class="w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="">{{ __('All topics') }}</option>
                        @foreach ($topics as $topic)
                            <option value="{{ $topic->id }}" @selected((string) request('topic') === (string) $topic->id)>{{ $topic->name }}</option>
                            @foreach ($topic->children as $child)
                                <option value="{{ $child->id }}" @selected((string) request('topic') === (string) $child->id)>— {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="difficulty" class="sr-only">{{ __('Difficulty') }}</label>
                    <select id="difficulty" name="difficulty" class="w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="">{{ __('All levels') }}</option>
                        @foreach (\Modules\BookIntelligence\Models\Book::DIFFICULTIES as $difficulty)
                            <option value="{{ $difficulty }}" @selected(request('difficulty') === $difficulty)>{{ __(ucfirst($difficulty)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="reading_status" class="sr-only">{{ __('Reading status') }}</label>
                    <select id="reading_status" name="reading_status" class="w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="">{{ __('Any reading status') }}</option>
                        <option value="planned" @selected(request('reading_status') === 'planned')>{{ __('Planned Reading') }}</option>
                        <option value="reading" @selected(request('reading_status') === 'reading')>{{ __('Currently Reading') }}</option>
                        <option value="read" @selected(request('reading_status') === 'read')>{{ __('Read') }}</option>
                        <option value="unassigned" @selected(request('reading_status') === 'unassigned')>{{ __('Not in reading plan') }}</option>
                    </select>
                </div>
                <div>
                    <label for="sort" class="sr-only">{{ __('Sort books') }}</label>
                    <select id="sort" name="sort" class="w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="alphabetical" @selected(request('sort', 'alphabetical') === 'alphabetical')>{{ __('A–Z') }}</option>
                        <option value="newest" @selected(request('sort') === 'newest')>{{ __('Recently added') }}</option>
                        <option value="publication_year" @selected(request('sort') === 'publication_year')>{{ __('Publication year') }}</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">{{ __('Apply filters') }}</button>
                @if (request()->hasAny(['q', 'topic', 'difficulty', 'reading_status', 'sort']))
                    <a href="{{ route('bookintelligence.books.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">{{ __('Clear filters') }}</a>
                @endif
                <span class="ml-auto text-sm text-slate-500">{{ trans_choice(':count book|:count books', $books->total(), ['count' => $books->total()]) }}</span>
            </div>
        </form>

        @if ($books->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-[#ff9200]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.966 8.966 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25A8.966 8.966 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.966 8.966 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <h2 class="mt-4 text-lg font-bold text-slate-900">{{ request()->hasAny(['q', 'topic', 'difficulty', 'reading_status']) ? __('No books match these filters') : __('Add the first book to your library') }}</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm text-slate-500">{{ __('Each book becomes an organized knowledge source with topics, role relevance, and reading progress.') }}</p>
                <a href="{{ route('bookintelligence.books.create') }}" class="mt-5 inline-flex rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">{{ __('Add book') }}</a>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($books as $book)
                    <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex gap-4">
                            @include('bookintelligence::partials.book-cover', ['book' => $book])
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @include('bookintelligence::partials.reading-status', ['status' => $book->readingStatus()])
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $book->analysis?->isCompleted() ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $book->analysis?->isCompleted() ? __('AI ready') : __('Needs analysis') }}
                                    </span>
                                </div>
                                <h2 class="mt-3 line-clamp-2 text-lg font-bold leading-snug text-slate-900">
                                    <a href="{{ route('bookintelligence.books.show', $book) }}" class="hover:text-[#ff9200]">{{ $book->title }}</a>
                                </h2>
                                <p class="mt-1 truncate text-sm text-slate-500">{{ $book->author?->name ?? __('Author not set') }}</p>
                                <dl class="mt-4 space-y-2 text-xs">
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-400">{{ __('Topic') }}</dt>
                                        <dd class="truncate font-medium text-slate-700">{{ $book->mainTopic?->name ?? __('Uncategorized') }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-400">{{ __('Difficulty') }}</dt>
                                        <dd class="font-medium capitalize text-slate-700">{{ __($book->difficulty) }}</dd>
                                    </div>
                                    @if ($book->publication_year)
                                        <div class="flex justify-between gap-3">
                                            <dt class="text-slate-400">{{ __('Published') }}</dt>
                                            <dd class="font-medium text-slate-700">{{ $book->publication_year }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                        @if ($book->currentUserProgress && $book->currentUserProgress->status === 'reading')
                            <div class="mt-5">
                                <div class="flex justify-between text-xs font-medium text-slate-500">
                                    <span>{{ __('Reading progress') }}</span>
                                    <span>{{ $book->currentUserProgress->progress_percent }}%</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-[#ff9200]" style="width: {{ $book->currentUserProgress->progress_percent }}%"></div>
                                </div>
                            </div>
                        @endif
                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                            <span class="text-xs text-slate-500">
                                {{ count($book->relevant_roles ?? []) ? implode(', ', array_slice($book->relevant_roles, 0, 2)) : __('All roles') }}
                            </span>
                            <a href="{{ route('bookintelligence.books.show', $book) }}" class="text-sm font-semibold text-[#0094af] hover:underline">{{ __('Open book') }}</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>{{ $books->links() }}</div>
        @endif
    </div>
@endsection
