@php $title = __('Library Setup'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6">
        @include('bookintelligence::partials.validation-errors')

        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Knowledge structure') }}</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('Library Setup') }}</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">{{ __('Create reusable authors, publishers, and topic categories once. They will keep every future book consistent and easy to find.') }}</p>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-[#ff9200]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </span>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">{{ __('Authors') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Who created the knowledge?') }}</p>
                </div>
                <form method="POST" action="{{ route('bookintelligence.setup.authors.store') }}" class="space-y-4 border-b border-slate-100 p-5">
                    @csrf
                    <div>
                        <label for="author_name" class="block text-sm font-semibold text-slate-700">{{ __('Author name') }}</label>
                        <input id="author_name" name="name" type="text" required placeholder="{{ __('Full name') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label for="author_website" class="block text-sm font-semibold text-slate-700">{{ __('Website') }}</label>
                        <input id="author_website" name="website" type="url" placeholder="https://..." class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label for="author_bio" class="block text-sm font-semibold text-slate-700">{{ __('Short bio') }}</label>
                        <textarea id="author_bio" name="bio" rows="2" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]"></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">{{ __('Add author') }}</button>
                </form>
                <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                    @forelse ($authors as $author)
                        <div class="flex items-center justify-between gap-3 p-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $author->name }}</p>
                                <p class="text-xs text-slate-500">{{ trans_choice(':count book|:count books', $author->books_count, ['count' => $author->books_count]) }}</p>
                            </div>
                            <form method="POST" action="{{ route('bookintelligence.setup.authors.destroy', $author) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700" aria-label="{{ __('Delete :name', ['name' => $author->name]) }}">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    @empty
                        <p class="p-5 text-sm text-slate-500">{{ __('No authors added yet.') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-[#0094af]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 12h6m-6 5.25h6" /></svg>
                    </span>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">{{ __('Publishers') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Who published the book?') }}</p>
                </div>
                <form method="POST" action="{{ route('bookintelligence.setup.publishers.store') }}" class="space-y-4 border-b border-slate-100 p-5">
                    @csrf
                    <div>
                        <label for="publisher_name" class="block text-sm font-semibold text-slate-700">{{ __('Publisher name') }}</label>
                        <input id="publisher_name" name="name" type="text" required placeholder="{{ __('Company name') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label for="publisher_website" class="block text-sm font-semibold text-slate-700">{{ __('Website') }}</label>
                        <input id="publisher_website" name="website" type="url" placeholder="https://..." class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">{{ __('Add publisher') }}</button>
                </form>
                <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                    @forelse ($publishers as $publisher)
                        <div class="flex items-center justify-between gap-3 p-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $publisher->name }}</p>
                                <p class="text-xs text-slate-500">{{ trans_choice(':count book|:count books', $publisher->books_count, ['count' => $publisher->books_count]) }}</p>
                            </div>
                            <form method="POST" action="{{ route('bookintelligence.setup.publishers.destroy', $publisher) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700" aria-label="{{ __('Delete :name', ['name' => $publisher->name]) }}">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    @empty
                        <p class="p-5 text-sm text-slate-500">{{ __('No publishers added yet.') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3.057A9 9 0 112.05 12.96M9.568 3.057A9.003 9.003 0 002.05 12.96m7.518-9.903c.763.9 1.432 1.945 1.988 3.09m-9.506 6.813a9.003 9.003 0 005.06 6.385m0 0a9.003 9.003 0 0010.83-2.995m-10.83 2.995c.342-1.102.79-2.182 1.34-3.223m9.49.228a9 9 0 00-6.383-10.203m6.383 10.203a16.92 16.92 0 00-9.49-.228m3.107-9.975a16.93 16.93 0 00-9.506 6.813m9.506-6.813a16.933 16.933 0 015.779 9.051M2.05 12.96a16.933 16.933 0 016.4 3.162" /></svg>
                    </span>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">{{ __('Topics') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('What knowledge area does it cover?') }}</p>
                </div>
                <form method="POST" action="{{ route('bookintelligence.setup.topics.store') }}" class="space-y-4 border-b border-slate-100 p-5">
                    @csrf
                    <div>
                        <label for="topic_name" class="block text-sm font-semibold text-slate-700">{{ __('Topic name') }}</label>
                        <input id="topic_name" name="name" type="text" required placeholder="{{ __('e.g. Sales Leadership') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label for="parent_id" class="block text-sm font-semibold text-slate-700">{{ __('Parent topic') }}</label>
                        <select id="parent_id" name="parent_id" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                            <option value="">{{ __('None — create a main topic') }}</option>
                            @foreach ($topics->whereNull('parent_id') as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">{{ __('Choose a parent only when creating a subtopic.') }}</p>
                    </div>
                    <div>
                        <label for="topic_description" class="block text-sm font-semibold text-slate-700">{{ __('Description') }}</label>
                        <textarea id="topic_description" name="description" rows="2" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]"></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">{{ __('Add topic') }}</button>
                </form>
                <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                    @forelse ($topics as $topic)
                        <div class="flex items-center justify-between gap-3 p-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $topic->parent ? '— '.$topic->name : $topic->name }}</p>
                                <p class="text-xs text-slate-500">{{ $topic->parent?->name ?? __('Main topic') }} · {{ $topic->main_books_count + $topic->books_count }} {{ __('uses') }}</p>
                            </div>
                            <form method="POST" action="{{ route('bookintelligence.setup.topics.destroy', $topic) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700" aria-label="{{ __('Delete :name', ['name' => $topic->name]) }}">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    @empty
                        <p class="p-5 text-sm text-slate-500">{{ __('No topics added yet.') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
            <h2 class="font-bold text-blue-900">{{ __('Recommended setup order') }}</h2>
            <p class="mt-1 text-sm leading-6 text-blue-700">{{ __('Create broad main topics first, add precise subtopics beneath them, then add authors and publishers as books enter the library. You do not need to configure everything upfront.') }}</p>
        </div>
    </div>
@endsection
