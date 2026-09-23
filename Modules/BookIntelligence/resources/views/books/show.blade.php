@php
    $title = $book->title;
    $analysis = $book->analysis;
@endphp
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

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50 px-6 py-5 sm:px-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('AI Book Intelligence') }}</p>
                            @if ($analysis)
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $analysis->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($analysis->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700') }}">
                                    {{ $analysis->statusLabel() }}
                                </span>
                            @endif
                        </div>
                        <h2 class="mt-1 text-2xl font-bold text-slate-900">{{ __('Turn this book into practical knowledge') }}</h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">{{ __('Allocore prepares a quick summary, deeper explanation, key lessons, reusable frameworks, and immediate actions for your team.') }}</p>
                    </div>
                    @if ($analysis?->generated_at)
                        <div class="text-right text-xs text-slate-500">
                            <p>{{ __('Last generated') }} {{ $analysis->generated_at->diffForHumans() }}</p>
                            @if ($analysis->provider)
                                <p class="mt-1">{{ __('Provider') }}: {{ $analysis->provider }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-5 p-6 sm:p-8">
                @if (! $aiConfigured)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                        <h3 class="font-semibold text-amber-900">{{ __('AI provider setup required') }}</h3>
                        <p class="mt-1 text-sm leading-6 text-amber-800">{{ __('Ask an administrator to configure Gemini, OpenAI, or Anthropic in Allocore AI settings before generating book intelligence.') }}</p>
                    </div>
                @elseif ($analysis?->isInProgress())
                    <div
                        class="rounded-2xl border border-orange-200 bg-orange-50 p-5"
                        x-data="{
                            poll() {
                                fetch(@js(route('bookintelligence.books.analysis.status', $book)), {
                                    headers: { 'Accept': 'application/json' }
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.is_in_progress) {
                                            setTimeout(() => this.poll(), 3000)
                                        } else {
                                            window.location.reload()
                                        }
                                    })
                                    .catch(() => setTimeout(() => this.poll(), 5000))
                            }
                        }"
                        x-init="setTimeout(() => poll(), 2500)"
                    >
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 animate-spin text-[#ff9200]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-orange-900">{{ __('Allocore is analyzing this book') }}</h3>
                                <p class="mt-1 text-sm leading-6 text-orange-800">{{ __('You can keep working. This page will refresh automatically when the intelligence is ready.') }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    @if ($analysis?->isOutdated())
                        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                            <h3 class="font-semibold text-blue-900">{{ __('The book details changed after this analysis') }}</h3>
                            <p class="mt-1 text-sm leading-6 text-blue-800">{{ __('Regenerate the intelligence so summaries and recommendations reflect the latest metadata and source notes.') }}</p>
                        </div>
                    @endif

                    @if ($analysis?->status === 'failed')
                        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                            <h3 class="font-semibold text-red-900">{{ __('The analysis could not be completed') }}</h3>
                            <p class="mt-1 text-sm leading-6 text-red-800">{{ __('Review the source information and try again. If the problem continues, ask an administrator to check the AI provider.') }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('bookintelligence.books.analysis.store', $book) }}" class="rounded-2xl border border-slate-200 bg-white p-5">
                        @csrf
                        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                            <div>
                                <label for="source_material" class="block text-sm font-semibold text-slate-800">{{ __('Optional source notes for a more accurate analysis') }}</label>
                                <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('Paste your notes, table of contents, learning brief, or approved excerpts. Allocore combines them with the saved book metadata.') }}</p>
                                <textarea id="source_material" name="source_material" rows="4" maxlength="60000" placeholder="{{ __('Add trusted context when the title and description are not enough...') }}" class="mt-3 w-full rounded-xl border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('source_material', $analysis?->source_material) }}</textarea>
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#ff9200] px-5 py-3 text-sm font-semibold text-white hover:bg-orange-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.456-2.456L14.25 6l1.035-.259a3.375 3.375 0 0 0 2.456-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" /></svg>
                                {{ $analysis?->generated_at ? __('Regenerate intelligence') : __('Generate intelligence') }}
                            </button>
                        </div>
                        <p class="mt-3 text-xs leading-5 text-slate-400">{{ __('AI output can contain mistakes. Verify important claims before using them in decisions or published content.') }}</p>
                    </form>
                @endif

                @if (filled($analysis?->short_summary))
                    <div class="grid gap-5 xl:grid-cols-[1.05fr_1fr]">
                        <article class="rounded-2xl border border-orange-200 bg-orange-50 p-6">
                            <p class="text-xs font-semibold uppercase tracking-wider text-orange-700">{{ __('1–2 minute read') }}</p>
                            <h3 class="mt-1 text-xl font-bold text-orange-950">{{ __('Short summary') }}</h3>
                            <div class="mt-4 whitespace-pre-line text-sm leading-7 text-orange-900">{{ $analysis->short_summary }}</div>
                        </article>

                        <details class="group rounded-2xl border border-slate-200 bg-slate-50 p-6">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Deep understanding') }}</p>
                                    <h3 class="mt-1 text-xl font-bold text-slate-900">{{ __('Long summary') }}</h3>
                                </div>
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition group-open:rotate-180">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                </span>
                            </summary>
                            <div class="mt-5 whitespace-pre-line border-t border-slate-200 pt-5 text-sm leading-7 text-slate-600">{{ $analysis->long_summary }}</div>
                        </details>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-2">
                        <section class="rounded-2xl border border-slate-200 bg-white p-6">
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Core lessons') }}</p>
                            <h3 class="mt-1 text-xl font-bold text-slate-900">{{ __('Key takeaways') }}</h3>
                            <div class="mt-5 space-y-4">
                                @foreach ($analysis->key_takeaways ?? [] as $takeaway)
                                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <h4 class="font-bold text-slate-900">{{ $takeaway['title'] }}</h4>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $takeaway['insight'] }}</p>
                                        @if (filled($takeaway['why_it_matters'] ?? null))
                                            <p class="mt-3 border-t border-slate-200 pt-3 text-xs leading-5 text-slate-500"><span class="font-semibold text-slate-700">{{ __('Why it matters') }}:</span> {{ $takeaway['why_it_matters'] }}</p>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 bg-white p-6">
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Apply the knowledge') }}</p>
                            <h3 class="mt-1 text-xl font-bold text-slate-900">{{ __('Actionable recommendations') }}</h3>
                            <ol class="mt-5 space-y-4">
                                @foreach ($analysis->actionable_recommendations ?? [] as $recommendation)
                                    <li class="grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-[36px_1fr]">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">{{ $loop->iteration }}</span>
                                        <div>
                                            <div class="flex flex-wrap items-start justify-between gap-2">
                                                <h4 class="font-bold text-slate-900">{{ $recommendation['action'] }}</h4>
                                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ ($recommendation['priority'] ?? 'medium') === 'high' ? 'bg-red-100 text-red-700' : (($recommendation['priority'] ?? 'medium') === 'low' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700') }}">{{ __($recommendation['priority'] ?? 'medium') }}</span>
                                            </div>
                                            @if (filled($recommendation['why'] ?? null))
                                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $recommendation['why'] }}</p>
                                            @endif
                                            <p class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800"><span class="font-semibold">{{ __('First step') }}:</span> {{ $recommendation['first_step'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    </div>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Reusable knowledge') }}</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">{{ __('Frameworks, processes, and checklists') }}</h3>
                        @if (! empty($analysis->frameworks))
                            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                                @foreach ($analysis->frameworks as $framework)
                                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <h4 class="font-bold text-slate-900">{{ $framework['name'] }}</h4>
                                            <span class="rounded-full bg-[#0094af]/10 px-2.5 py-1 text-xs font-semibold capitalize text-[#00768a]">{{ __($framework['type']) }}</span>
                                        </div>
                                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $framework['description'] }}</p>
                                        @if (! empty($framework['steps']))
                                            <ol class="mt-4 space-y-2">
                                                @foreach ($framework['steps'] as $step)
                                                    <li class="flex gap-2 text-sm text-slate-700">
                                                        <span class="font-bold text-[#ff9200]">{{ $loop->iteration }}.</span>
                                                        <span>{{ $step }}</span>
                                                    </li>
                                                @endforeach
                                            </ol>
                                        @endif
                                        @if (filled($framework['when_to_use'] ?? null))
                                            <p class="mt-4 border-t border-slate-200 pt-3 text-xs leading-5 text-slate-500"><span class="font-semibold text-slate-700">{{ __('When to use it') }}:</span> {{ $framework['when_to_use'] }}</p>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">{{ __('No explicit framework could be verified from the available book context.') }}</div>
                        @endif
                    </section>
                @endif
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

                <!-- Module 3: Question Mappings & FAQs Section -->
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 3 — Question Mapping Engine') }}</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('Questions Answered & Problems Solved') }}</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('bookintelligence.questions.generate', $book) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-[#0094af] px-4 py-2 text-xs font-semibold text-white hover:bg-[#007a90] shadow-sm">
                                    ⚡ {{ __('Extract AI FAQs & Mappings') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse ($book->questionMappings as $mapping)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-sm font-bold text-slate-900">❓ {{ $mapping->question }}</h3>
                                    <span class="rounded bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-800">{{ $mapping->category ?? 'General' }}</span>
                                </div>
                                <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                    {{ $mapping->answer_excerpt }}
                                </p>
                                @if ($mapping->when_to_read_trigger)
                                    <p class="mt-2 text-[11px] font-medium text-amber-800 bg-amber-50 rounded p-1.5">
                                        ⏱️ <strong>{{ __('When to Read:') }}</strong> {{ $mapping->when_to_read_trigger }}
                                    </p>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-500">
                                <p>{{ __('No question mappings generated yet for this book.') }}</p>
                                <p class="mt-1 text-slate-400">{{ __('Click "Extract AI FAQs & Mappings" above to automatically generate 5–10 authoritative business questions and triggers.') }}</p>
                            </div>
                        @endforelse
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
                            <input id="progress_percent" name="progress_percent" type="range" min="0" max="100" step="1" x-model.number="progress" class="mt-3 w-full accent-[#ff9200]">
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
