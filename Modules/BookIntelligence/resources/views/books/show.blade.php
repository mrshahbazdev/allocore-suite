@php $title = $book->title; @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6" data-no-navigate>
        @include('bookintelligence::partials.validation-errors')

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('bookintelligence.books.index') }}" class="inline-flex items-center text-sm font-semibold text-slate-500 hover:text-slate-900">
                <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                {{ __('Back to library') }}
            </a>
            <div class="flex gap-2">
                <a href="{{ route('bookintelligence.books.edit', $book) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Edit book') }}</a>
                <form method="POST" action="{{ route('bookintelligence.books.destroy', $book) }}" onsubmit="return confirm('{{ __('Remove this book from the library?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-8 p-6 sm:grid-cols-[auto_1fr] sm:p-8">
                <div class="mx-auto sm:mx-0">
                    @include('bookintelligence::partials.book-cover', ['book' => $book, 'size' => 'large'])
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        @include('bookintelligence::partials.reading-status', ['status' => $book->readingStatus()])
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-600">{{ __($book->difficulty) }}</span>
                        @if ($book->mainTopic)
                            <span class="inline-flex rounded-full bg-[#0094af]/10 px-2.5 py-1 text-xs font-semibold text-[#00768a]">{{ $book->mainTopic->name }}</span>
                        @endif
                    </div>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ $book->title }}</h1>
                    <p class="mt-2 text-lg text-slate-500">{{ $book->author?->name ?? __('Author not set') }}</p>
                    @if ($book->description)
                        <p class="mt-6 max-w-3xl whitespace-pre-line text-sm leading-7 text-slate-600">{{ $book->description }}</p>
                    @else
                        <div class="mt-6 max-w-2xl rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                            {{ __('Add a short explanation of the problems this book solves to help teammates understand when to read it.') }}
                            <a href="{{ route('bookintelligence.books.edit', $book) }}" class="ml-1 font-semibold text-[#0094af] hover:underline">{{ __('Add description') }}</a>
                        </div>
                    @endif

                    <dl class="mt-8 grid gap-x-8 gap-y-5 border-t border-slate-100 pt-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Publisher') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $book->publisher?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Published') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $book->publication_year ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Pages') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $book->page_count ? number_format($book->page_count) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('ISBN') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $book->isbn ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Knowledge classification') }}</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('Topics and audience') }}</h2>
                    <div class="mt-5">
                        <h3 class="text-sm font-semibold text-slate-700">{{ __('Subtopics') }}</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($book->subtopics as $topic)
                                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-sm text-slate-700">{{ $topic->name }}</span>
                            @empty
                                <p class="text-sm text-slate-500">{{ __('No subtopics assigned.') }}</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="mt-6 border-t border-slate-100 pt-5">
                        <h3 class="text-sm font-semibold text-slate-700">{{ __('Relevant job roles') }}</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($book->relevant_roles ?? [] as $role)
                                <span class="rounded-full bg-orange-50 px-3 py-1.5 text-sm font-medium text-orange-700">{{ $role }}</span>
                            @empty
                                <p class="text-sm text-slate-500">{{ __('Relevant roles have not been specified yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Resources') }}</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('Book access') }}</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        @if ($book->affiliate_link)
                            <a href="{{ $book->affiliate_link }}" target="_blank" rel="noopener noreferrer sponsored" class="rounded-xl border border-orange-200 bg-orange-50 p-4 hover:bg-orange-100">
                                <p class="font-semibold text-orange-800">{{ __('Purchase this book') }}</p>
                                <p class="mt-1 text-sm text-orange-700">{{ __('Open the saved affiliate link') }} ↗</p>
                            </a>
                        @endif
                        @if (! $book->affiliate_link)
                            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4">
                                <p class="font-semibold text-slate-700">{{ __('No purchase link saved') }}</p>
                                <a href="{{ route('bookintelligence.books.edit', $book) }}" class="mt-1 inline-flex text-sm font-semibold text-[#0094af] hover:underline">{{ __('Add affiliate link') }}</a>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <aside>
                <form method="POST" action="{{ route('bookintelligence.books.reading-progress', $book) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    @csrf
                    @method('PUT')
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Personal learning') }}</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('Reading progress') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Keep this current so your dashboard always shows the next book to focus on.') }}</p>
                    <div class="mt-5 space-y-4" x-data="{ progress: @js((int) old('progress_percent', $book->currentUserProgress?->progress_percent ?? 0)) }">
                        <div>
                            <label for="reading_status" class="block text-sm font-semibold text-slate-700">{{ __('Status') }}</label>
                            <select id="reading_status" name="reading_status" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <option value="unassigned" @selected($book->readingStatus() === 'unassigned')>{{ __('Not in reading plan') }}</option>
                                <option value="planned" @selected($book->readingStatus() === 'planned')>{{ __('Planned Reading') }}</option>
                                <option value="reading" @selected($book->readingStatus() === 'reading')>{{ __('Currently Reading') }}</option>
                                <option value="read" @selected($book->readingStatus() === 'read')>{{ __('Read') }}</option>
                            </select>
                        </div>
                        <div>
                            <div class="flex items-center justify-between">
                                <label for="progress_percent" class="block text-sm font-semibold text-slate-700">{{ __('Progress') }}</label>
                                <span class="text-sm font-bold text-[#ff9200]" x-text="`${progress}%`">{{ old('progress_percent', $book->currentUserProgress?->progress_percent ?? 0) }}%</span>
                            </div>
                            <input id="progress_percent" name="progress_percent" type="range" min="0" max="100" step="5" x-model.number="progress" class="mt-3 w-full accent-[#ff9200]">
                        </div>
                        <div>
                            <label for="reading_notes" class="block text-sm font-semibold text-slate-700">{{ __('Notes') }}</label>
                            <textarea id="reading_notes" name="reading_notes" rows="5" placeholder="{{ __('Capture your learning goal, takeaway, or next action.') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('reading_notes', $book->currentUserProgress?->notes) }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="mt-5 w-full rounded-xl bg-[#ff9200] px-5 py-3 text-sm font-semibold text-white hover:bg-orange-600">{{ __('Update progress') }}</button>
                </form>
            </aside>
        </div>
    </div>
@endsection
