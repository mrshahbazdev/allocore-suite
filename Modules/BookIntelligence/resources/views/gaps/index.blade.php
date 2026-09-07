@php $title = __('Knowledge Gap Detection & AI Recommendations'); @endphp
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
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 5 — Knowledge Gap Detection') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('Missing Knowledge & AI Acquisition Advisor') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    {{ __('Automatically identifies questions asked by your team that current library literature cannot answer, and uses AI to recommend top-tier published books to acquire.') }}
                </p>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-400">{{ __('Total Gaps Logged') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-amber-700">{{ __('Open / Unresolved') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-amber-900">{{ $stats['open'] }}</p>
            </div>
            <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-blue-700">{{ __('In Review') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-blue-900">{{ $stats['reviewing'] }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-emerald-700">{{ __('Resolved (Acquired)') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-emerald-900">{{ $stats['resolved'] }}</p>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
            <a href="{{ route('bookintelligence.gaps.index', ['status' => 'all']) }}" class="rounded-lg px-3.5 py-1.5 text-xs font-semibold {{ $status === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                {{ __('All') }}
            </a>
            <a href="{{ route('bookintelligence.gaps.index', ['status' => 'open']) }}" class="rounded-lg px-3.5 py-1.5 text-xs font-semibold {{ $status === 'open' ? 'bg-amber-500 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                {{ __('Open') }} ({{ $stats['open'] }})
            </a>
            <a href="{{ route('bookintelligence.gaps.index', ['status' => 'reviewing']) }}" class="rounded-lg px-3.5 py-1.5 text-xs font-semibold {{ $status === 'reviewing' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                {{ __('Reviewing') }} ({{ $stats['reviewing'] }})
            </a>
            <a href="{{ route('bookintelligence.gaps.index', ['status' => 'resolved']) }}" class="rounded-lg px-3.5 py-1.5 text-xs font-semibold {{ $status === 'resolved' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                {{ __('Resolved') }} ({{ $stats['resolved'] }})
            </a>
        </div>

        <!-- Gaps List -->
        @if ($gaps->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-2xl text-emerald-600">
                    ✨
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900">{{ __('No Knowledge Gaps in this View') }}</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    {{ __('Whenever team members search for topics not yet covered in the library, they will automatically appear here alongside AI book acquisition recommendations.') }}
                </p>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($gaps as $gap)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <!-- Top bar: query, frequency, status -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                                        🔥 {{ trans_choice(':count search|:count searches', $gap->search_count, ['count' => $gap->search_count]) }}
                                    </span>
                                    <span class="text-xs text-slate-400">• {{ $gap->created_at->diffForHumans() }}</span>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">
                                    "{{ $gap->query }}"
                                </h3>
                            </div>

                            <!-- Status changer -->
                            <form method="POST" action="{{ route('bookintelligence.gaps.status', $gap->id) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-xs font-semibold focus:border-[#ff9200] focus:ring-[#ff9200]">
                                    <option value="open" @selected($gap->status === 'open')>🟡 {{ __('Open') }}</option>
                                    <option value="reviewing" @selected($gap->status === 'reviewing')>🔵 {{ __('Reviewing') }}</option>
                                    <option value="resolved" @selected($gap->status === 'resolved')>🟢 {{ __('Resolved') }}</option>
                                    <option value="dismissed" @selected($gap->status === 'dismissed')>⚪ {{ __('Dismissed') }}</option>
                                </select>
                            </form>
                        </div>

                        <!-- AI Book Acquisition Recommendations -->
                        <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-[#0094af] flex items-center gap-1.5">
                                    <span>🤖</span> {{ __('AI Recommended Books to Fill this Gap') }}
                                </h4>
                                <form method="POST" action="{{ route('bookintelligence.gaps.recommendations', $gap->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-slate-500 hover:text-slate-800">
                                        🔄 {{ __('Regenerate') }}
                                    </button>
                                </form>
                            </div>

                            @if (!empty($gap->suggested_books))
                                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    @foreach ($gap->suggested_books as $book)
                                        <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                            <div class="space-y-2">
                                                <div class="flex items-start justify-between gap-2">
                                                    <h5 class="text-sm font-bold text-slate-900 leading-snug">{{ $book['title'] }}</h5>
                                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-600">{{ $book['difficulty'] ?? 'Level' }}</span>
                                                </div>
                                                <p class="text-xs font-medium text-slate-500">{{ __('Author:') }} {{ $book['author'] }}</p>
                                                <p class="text-xs text-slate-600 leading-relaxed">{{ $book['reason_for_recommendation'] }}</p>
                                                @if (!empty($book['key_concept']))
                                                    <p class="text-[11px] text-amber-800 bg-amber-50 rounded p-1.5">
                                                        💡 <strong>{{ __('Key Model:') }}</strong> {{ $book['key_concept'] }}
                                                    </p>
                                                @endif
                                            </div>

                                            <form method="POST" action="{{ route('bookintelligence.gaps.import', $gap->id) }}" class="mt-4 pt-3 border-t border-slate-100">
                                                @csrf
                                                <input type="hidden" name="title" value="{{ $book['title'] }}">
                                                <input type="hidden" name="author" value="{{ $book['author'] }}">
                                                <input type="hidden" name="topic" value="{{ $book['topic'] ?? 'General Business' }}">
                                                <input type="hidden" name="difficulty" value="{{ $book['difficulty'] ?? 'intermediate' }}">
                                                <input type="hidden" name="reason_for_recommendation" value="{{ $book['reason_for_recommendation'] ?? '' }}">
                                                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-sm">
                                                    + {{ __('Add to Planned Reading') }}
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-xs text-slate-500">{{ __('No recommendations generated yet. Click Regenerate above.') }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="mt-6">
                    {{ $gaps->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
