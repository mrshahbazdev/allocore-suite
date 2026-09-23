@php($editing = $book !== null)
@php($title = $editing ? __('Edit Book') : __('Add Book'))
@php($selectedSubtopics = collect(old('subtopic_ids', $book?->subtopics?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())
@php($progress = $book?->currentUserProgress)
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="mx-auto max-w-6xl" data-no-navigate>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Book Library') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ $editing ? __('Edit book details') : __('Add a knowledge source') }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">{{ __('Start with the essentials. Optional details make the book easier for others to discover and use.') }}</p>
            </div>
            <a href="{{ $editing ? route('bookintelligence.books.show', $book) : route('bookintelligence.books.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>

        <div class="mt-6">
            @include('bookintelligence::partials.validation-errors')
        </div>

        @if ($authors->isEmpty() || $topics->isEmpty())
            <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-blue-200 bg-blue-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-blue-900">{{ __('Want a cleaner library?') }}</p>
                    <p class="mt-1 text-sm text-blue-700">{{ __('Add authors and topics first, or continue now and organize this book later.') }}</p>
                </div>
                <a href="{{ route('bookintelligence.setup.index') }}" class="shrink-0 text-sm font-semibold text-blue-800 hover:underline">{{ __('Open library setup') }}</a>
            </div>
        @endif

        <form method="POST" action="{{ $editing ? route('bookintelligence.books.update', $book) : route('bookintelligence.books.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Step 1 · Identity') }}</span>
                        <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('What is this book?') }}</h2>
                    </div>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-semibold text-slate-700">{{ __('Book title') }} <span class="text-red-500">*</span></label>
                            <input id="title" name="title" type="text" required value="{{ old('title', $book?->title) }}" placeholder="{{ __('e.g. Scaling Up') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div x-data="{ addingNewAuthor: {{ old('new_author_name') ? 'true' : 'false' }} }">
                            <div class="flex items-center justify-between">
                                <label for="author_id" class="block text-sm font-semibold text-slate-700">{{ __('Author') }}</label>
                                <button type="button" @click="addingNewAuthor = !addingNewAuthor" class="text-xs font-semibold text-[#0094af] hover:underline">
                                    <span x-show="!addingNewAuthor">+ {{ __('Neuen Autor anlegen') }}</span>
                                    <span x-show="addingNewAuthor" x-cloak>&larr; {{ __('Aus Liste wählen') }}</span>
                                </button>
                            </div>
                            <div x-show="!addingNewAuthor">
                                <select id="author_id" name="author_id" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                                    <option value="">{{ __('Select an author') }}</option>
                                    @foreach ($authors as $author)
                                        <option value="{{ $author->id }}" @selected((string) old('author_id', $book?->author_id) === (string) $author->id)>{{ $author->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="addingNewAuthor" x-cloak class="mt-2 space-y-1">
                                <input type="text" name="new_author_name" value="{{ old('new_author_name') }}" placeholder="{{ __('Name des neuen Autors...') }}" class="w-full rounded-xl border-orange-300 bg-orange-50/40 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <p class="text-[11px] text-slate-500">{{ __('Wird beim Speichern automatisch erstellt.') }}</p>
                            </div>
                        </div>

                        <div x-data="{ addingNewPublisher: {{ old('new_publisher_name') ? 'true' : 'false' }} }">
                            <div class="flex items-center justify-between">
                                <label for="publisher_id" class="block text-sm font-semibold text-slate-700">{{ __('Publisher') }}</label>
                                <button type="button" @click="addingNewPublisher = !addingNewPublisher" class="text-xs font-semibold text-[#0094af] hover:underline">
                                    <span x-show="!addingNewPublisher">+ {{ __('Neuen Verlag anlegen') }}</span>
                                    <span x-show="addingNewPublisher" x-cloak>&larr; {{ __('Aus Liste wählen') }}</span>
                                </button>
                            </div>
                            <div x-show="!addingNewPublisher">
                                <select id="publisher_id" name="publisher_id" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                                    <option value="">{{ __('Select a publisher') }}</option>
                                    @foreach ($publishers as $publisher)
                                        <option value="{{ $publisher->id }}" @selected((string) old('publisher_id', $book?->publisher_id) === (string) $publisher->id)>{{ $publisher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="addingNewPublisher" x-cloak class="mt-2 space-y-1">
                                <input type="text" name="new_publisher_name" value="{{ old('new_publisher_name') }}" placeholder="{{ __('Name des neuen Verlags...') }}" class="w-full rounded-xl border-orange-300 bg-orange-50/40 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <p class="text-[11px] text-slate-500">{{ __('Wird beim Speichern automatisch erstellt.') }}</p>
                            </div>
                        </div>

                        <div>
                            <label for="isbn" class="block text-sm font-semibold text-slate-700">{{ __('ISBN') }}</label>
                            <input id="isbn" name="isbn" type="text" value="{{ old('isbn', $book?->isbn) }}" placeholder="978-..." class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                        </div>
                        <div>
                            <label for="publication_year" class="block text-sm font-semibold text-slate-700">{{ __('Publication year') }}</label>
                            <input id="publication_year" name="publication_year" type="number" min="1000" max="{{ now()->year + 1 }}" value="{{ old('publication_year', $book?->publication_year) }}" placeholder="{{ now()->year }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                        </div>
                        <div>
                            <label for="page_count" class="block text-sm font-semibold text-slate-700">{{ __('Page count') }}</label>
                            <input id="page_count" name="page_count" type="number" min="1" value="{{ old('page_count', $book?->page_count) }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                        </div>
                        <div>
                            <label for="language" class="block text-sm font-semibold text-slate-700">{{ __('Language') }} <span class="text-red-500">*</span></label>
                            <select id="language" name="language" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <option value="en" @selected(old('language', $book?->language ?? 'en') === 'en')>{{ __('English') }}</option>
                                <option value="de" @selected(old('language', $book?->language) === 'de')>{{ __('German') }}</option>
                                <option value="other" @selected(old('language', $book?->language) === 'other')>{{ __('Other') }}</option>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Step 2 · Discoverability') }}</span>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('Who is it for and what does it teach?') }}</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div x-data="{ addingNewTopic: {{ old('new_topic_name') ? 'true' : 'false' }} }">
                            <div class="flex items-center justify-between">
                                <label for="main_topic_id" class="block text-sm font-semibold text-slate-700">{{ __('Main topic') }}</label>
                                <button type="button" @click="addingNewTopic = !addingNewTopic" class="text-xs font-semibold text-[#0094af] hover:underline">
                                    <span x-show="!addingNewTopic">+ {{ __('Neues Thema anlegen') }}</span>
                                    <span x-show="addingNewTopic" x-cloak>&larr; {{ __('Aus Liste wählen') }}</span>
                                </button>
                            </div>
                            <div x-show="!addingNewTopic">
                                <select id="main_topic_id" name="main_topic_id" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                                    <option value="">{{ __('Select the main topic') }}</option>
                                    @foreach ($topics as $topic)
                                        <option value="{{ $topic->id }}" @selected((string) old('main_topic_id', $book?->main_topic_id) === (string) $topic->id)>{{ $topic->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="addingNewTopic" x-cloak class="mt-2 space-y-1">
                                <input type="text" name="new_topic_name" value="{{ old('new_topic_name') }}" placeholder="{{ __('Name des neuen Themas...') }}" class="w-full rounded-xl border-orange-300 bg-orange-50/40 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <p class="text-[11px] text-slate-500">{{ __('Wird beim Speichern automatisch erstellt.') }}</p>
                            </div>
                        </div>
                        <div>
                            <label for="difficulty" class="block text-sm font-semibold text-slate-700">{{ __('Difficulty level') }} <span class="text-red-500">*</span></label>
                            <select id="difficulty" name="difficulty" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                                @foreach (\Modules\BookIntelligence\Models\Book::DIFFICULTIES as $difficulty)
                                    <option value="{{ $difficulty }}" @selected(old('difficulty', $book?->difficulty ?? 'intermediate') === $difficulty)>{{ __(ucfirst($difficulty)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <fieldset class="sm:col-span-2">
                            <legend class="text-sm font-semibold text-slate-700">{{ __('Subtopics') }}</legend>
                            <p class="mt-1 text-xs text-slate-500">{{ __('Select every additional knowledge area covered by the book.') }}</p>
                            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                @forelse ($topics->flatMap(fn ($topic) => $topic->children) as $subtopic)
                                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 text-sm text-slate-700 hover:bg-slate-50">
                                        <input type="checkbox" name="subtopic_ids[]" value="{{ $subtopic->id }}" @checked(in_array((string) $subtopic->id, $selectedSubtopics, true)) class="rounded border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                                        <span>{{ $subtopic->name }}</span>
                                    </label>
                                @empty
                                    <p class="sm:col-span-2 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">{{ __('No subtopics yet. Add them in Library Setup if you need more precise classification.') }}</p>
                                @endforelse
                            </div>
                        </fieldset>
                        <div class="sm:col-span-2">
                            <label for="relevant_roles_text" class="block text-sm font-semibold text-slate-700">{{ __('Relevant job roles') }}</label>
                            <textarea id="relevant_roles_text" name="relevant_roles_text" rows="3" placeholder="{{ __('Sales, Customer Success, Leadership') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('relevant_roles_text', implode(', ', $book?->relevant_roles ?? [])) }}</textarea>
                            <p class="mt-1 text-xs text-slate-500">{{ __('Separate roles with commas or put each role on a new line.') }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-semibold text-slate-700">{{ __('Why this book matters') }}</label>
                            <textarea id="description" name="description" rows="5" placeholder="{{ __('Describe the problems this book helps solve and why the team should read it.') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('description', $book?->description) }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Step 3 · Resources') }}</span>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('Add optional links') }}</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="cover_url" class="block text-sm font-semibold text-slate-700">{{ __('Book cover URL') }}</label>
                            <input id="cover_url" name="cover_url" type="url" value="{{ old('cover_url', $book?->cover_url) }}" placeholder="https://..." class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                        </div>
                        <div>
                            <label for="affiliate_link" class="block text-sm font-semibold text-slate-700">{{ __('Affiliate purchase link') }}</label>
                            <input id="affiliate_link" name="affiliate_link" type="url" value="{{ old('affiliate_link', $book?->affiliate_link) }}" placeholder="https://..." class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Your reading plan') }}</p>
                    <h2 class="mt-1 font-bold text-slate-900">{{ __('Set a personal status') }}</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="reading_status" class="block text-sm font-semibold text-slate-700">{{ __('Reading status') }}</label>
                            <select id="reading_status" name="reading_status" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <option value="unassigned" @selected(old('reading_status', $progress?->status ?? 'unassigned') === 'unassigned')>{{ __('Not in reading plan') }}</option>
                                <option value="planned" @selected(old('reading_status', $progress?->status) === 'planned')>{{ __('Planned Reading') }}</option>
                                <option value="reading" @selected(old('reading_status', $progress?->status) === 'reading')>{{ __('Currently Reading') }}</option>
                                <option value="read" @selected(old('reading_status', $progress?->status) === 'read')>{{ __('Read') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="progress_percent" class="block text-sm font-semibold text-slate-700">{{ __('Progress percentage') }}</label>
                            <input id="progress_percent" name="progress_percent" type="number" min="0" max="100" value="{{ old('progress_percent', $progress?->progress_percent ?? 0) }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                            <p class="mt-1 text-xs text-slate-500">{{ __('Completed books are automatically set to 100%.') }}</p>
                        </div>
                        <div>
                            <label for="reading_notes" class="block text-sm font-semibold text-slate-700">{{ __('Personal notes') }}</label>
                            <textarea id="reading_notes" name="reading_notes" rows="4" placeholder="{{ __('What do you want to learn or remember?') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('reading_notes', $progress?->notes) }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <label for="status" class="block text-sm font-semibold text-slate-700">{{ __('Library visibility') }}</label>
                    <select id="status" name="status" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="active" @selected(old('status', $book?->status ?? 'active') === 'active')>{{ __('Active') }}</option>
                        <option value="archived" @selected(old('status', $book?->status) === 'archived')>{{ __('Archived') }}</option>
                    </select>
                    <button type="submit" class="mt-5 w-full rounded-xl bg-[#ff9200] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                        {{ $editing ? __('Save changes') : __('Add to library') }}
                    </button>
                    <p class="mt-3 text-center text-xs leading-5 text-slate-500">{{ __('You can update every detail later.') }}</p>
                </section>
            </aside>
        </form>
    </div>
@endsection
