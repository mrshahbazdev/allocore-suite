@php $title = __('Book-to-Content Repurposing Engine'); @endphp
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

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 9 — Book-to-Content Repurposing') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('Omnichannel Content Asset Generator') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    {{ __('Transform every book in your library into 20 Blog Articles, 50 LinkedIn Posts, 20 FAQs, 10 Checklists, 10 Practical Guides, and 5 Whitepapers.') }}
                </p>
            </div>
        </div>

        <!-- Books Grid for Repurposing -->
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($books as $book)
                <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                    <div class="space-y-3">
                        <div class="flex items-start gap-4">
                            @if ($book->cover_url)
                                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="h-24 w-16 rounded-lg object-cover shadow-sm">
                            @else
                                <div class="flex h-24 w-16 items-center justify-center rounded-lg bg-slate-100 text-2xl">📖</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <span class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 uppercase">{{ $book->mainTopic?->name ?? 'Topic' }}</span>
                                <h3 class="mt-1 text-base font-bold text-slate-900 leading-snug">{{ $book->title }}</h3>
                                <p class="text-xs text-slate-500">{{ $book->author?->name }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 text-center text-xs">
                            <div>
                                <span class="font-extrabold text-slate-900">20</span>
                                <p class="text-[10px] text-slate-400 uppercase">Blogs</p>
                            </div>
                            <div>
                                <span class="font-extrabold text-slate-900">50</span>
                                <p class="text-[10px] text-slate-400 uppercase">LinkedIn</p>
                            </div>
                            <div>
                                <span class="font-extrabold text-slate-900">25+</span>
                                <p class="text-[10px] text-slate-400 uppercase">Guides/Check</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 border-t border-slate-100 pt-4 flex items-center justify-between gap-3">
                        <a href="{{ route('bookintelligence.repurposing.show', $book->id) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            {{ __('View Content Assets') }}
                        </a>
                        <form method="POST" action="{{ route('bookintelligence.repurposing.generate', $book->id) }}">
                            @csrf
                            <button type="submit" class="rounded-xl bg-[#ff9200] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-orange-600">
                                ⚡ {{ __('Generate Bundle') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
