@php $title = __('Question Mapping & FAQ Engine'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6" data-no-navigate>
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 3 — Question Mapping Engine') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('AI-Generated FAQ & Problem Database') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    {{ __('Every book is automatically analyzed to extract answered questions, solved problems, target roles, and "When to Read" triggers linked to Allocore tools.') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('bookintelligence.questions.create') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    + {{ __('Manual Mapping') }}
                </a>
            </div>
        </div>

        <!-- Filter bar -->
        <form method="GET" action="{{ route('bookintelligence.questions.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid gap-3 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label for="search" class="sr-only">{{ __('Search questions') }}</label>
                    <input id="search" type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search questions, problems, triggers, or books...') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                </div>
                <div>
                    <label for="book_id" class="sr-only">{{ __('Filter by Book') }}</label>
                    <select id="book_id" name="book_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="">{{ __('All Books') }}</option>
                        @foreach ($books as $b)
                            <option value="{{ $b->id }}" @selected((string) $bookId === (string) $b->id)>{{ $b->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category" class="sr-only">{{ __('Category') }}</label>
                    <select id="category" name="category" class="w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected($category === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">{{ __('Filter') }}</button>
                    @if (request()->hasAny(['search', 'book_id', 'category']))
                        <a href="{{ route('bookintelligence.questions.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">{{ __('Clear') }}</a>
                    @endif
                </div>
                <span class="text-xs text-slate-500">{{ $mappings->total() }} {{ __('mappings available') }}</span>
            </div>
        </form>

        <!-- Generate for books banner if no mappings -->
        @if ($mappings->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-2xl text-amber-600">
                    💡
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900">{{ __('No Question Mappings Found') }}</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    {{ __('Generate AI question mappings from any book in your library to unlock automated FAQ discovery and audit guidance.') }}
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    @foreach ($books->take(3) as $bookToGen)
                        <form method="POST" action="{{ route('bookintelligence.questions.generate', $bookToGen->id) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#0094af] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#007b91]">
                                ⚡ {{ __('Generate for') }} {{ Str::limit($bookToGen->title, 20) }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Question Mappings Grid -->
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($mappings as $mapping)
                    <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                        <div class="space-y-4">
                            <!-- Category badge & Book link -->
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    {{ $mapping->category ?? 'General' }}
                                </span>
                                @if ($mapping->book)
                                    <a href="{{ route('bookintelligence.books.show', $mapping->book->id) }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900">
                                        📖 <span class="underline decoration-slate-300">{{ $mapping->book->title }}</span>
                                    </a>
                                @endif
                            </div>

                            <!-- Question Title -->
                            <div>
                                <h3 class="text-base font-bold text-slate-900">
                                    {{ $mapping->question }}
                                </h3>
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                                    {{ $mapping->answer_excerpt }}
                                </p>
                            </div>

                            <!-- Problem Solved & When to Read -->
                            <div class="space-y-2 rounded-xl bg-slate-50 p-3.5 text-xs text-slate-700">
                                @if ($mapping->problem_statement)
                                    <div>
                                        <strong class="font-semibold text-slate-900">{{ __('Problem Solved:') }}</strong>
                                        <span class="text-slate-600">{{ $mapping->problem_statement }}</span>
                                    </div>
                                @endif
                                @if ($mapping->when_to_read_trigger)
                                    <div>
                                        <strong class="font-semibold text-amber-900">{{ __('When to Read:') }}</strong>
                                        <span class="text-amber-800">{{ $mapping->when_to_read_trigger }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Target Audience Roles -->
                            @if (!empty($mapping->target_audience))
                                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                    <span class="text-xs font-semibold text-slate-400">{{ __('Target Roles:') }}</span>
                                    @foreach ($mapping->target_audience as $role)
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                            {{ $role }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Allocore Tool & Knowledge Linkage Badges -->
                            @if ($mapping->module_key || $mapping->glossaryTerm)
                                <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 text-xs">
                                    @if ($mapping->module_key)
                                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                                            🛠️ {{ __('Tool:') }} {{ ucfirst($mapping->module_key) }}
                                        </span>
                                    @endif
                                    @if ($mapping->glossaryTerm)
                                        <span class="inline-flex items-center gap-1 font-medium text-[#0094af]">
                                            📚 {{ __('Knowledge:') }} {{ $mapping->glossaryTerm->term }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Footer Actions -->
                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-xs">
                            <span class="text-slate-400">
                                {{ __('Priority') }}: <strong class="text-slate-700">{{ $mapping->priority }}</strong>
                            </span>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('bookintelligence.questions.edit', $mapping->id) }}" class="font-medium text-slate-600 hover:text-slate-900">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('bookintelligence.questions.destroy', $mapping->id) }}" onsubmit="return confirm('Delete this question mapping?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $mappings->links() }}
            </div>
        @endif
    </div>
@endsection
