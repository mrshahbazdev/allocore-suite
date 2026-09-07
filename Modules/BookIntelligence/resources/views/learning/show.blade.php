@php $title = $learningPath->title; @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="mx-auto max-w-4xl space-y-6" data-no-navigate>
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <a href="{{ route('bookintelligence.learning.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; {{ __('Back to Learning Paths') }}
        </a>

        <!-- Header Card -->
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <span class="rounded bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                    🎯 {{ __('Goal:') }} {{ $learningPath->targetRole?->name ?? 'Career Milestone' }}
                </span>
                <span class="text-sm font-extrabold text-[#ff9200]">{{ $learningPath->progress_percent }}% Completed</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900">{{ $learningPath->title }}</h1>
            <p class="text-sm text-slate-600 leading-relaxed">{{ $learningPath->ai_rationale }}</p>

            <div class="mt-4 h-2.5 w-full rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-[#ff9200] to-emerald-500 transition-all duration-500" style="width: {{ $learningPath->progress_percent }}%"></div>
            </div>
        </div>

        <!-- Sequential Steps Timeline -->
        <h2 class="text-xl font-bold text-slate-900 pt-2">{{ __('Sequential Learning Milestones') }}</h2>
        <div class="space-y-4">
            @foreach ($learningPath->steps ?? [] as $idx => $step)
                @php
                    $isCompleted = ($step['status'] ?? '') === 'completed';
                    $isInProgress = ($step['status'] ?? '') === 'in_progress';
                    $book = isset($step['book_id']) ? \Modules\BookIntelligence\Models\Book::find($step['book_id']) : null;
                @endphp
                <div class="rounded-2xl border {{ $isCompleted ? 'border-emerald-200 bg-emerald-50/30' : ($isInProgress ? 'border-amber-300 bg-white shadow-md' : 'border-slate-200 bg-white opacity-70') }} p-6 transition">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $isCompleted ? 'bg-emerald-600 text-white' : ($isInProgress ? 'bg-[#ff9200] text-white' : 'bg-slate-200 text-slate-600') }} font-extrabold text-sm">
                                {{ $isCompleted ? '✓' : ($idx + 1) }}
                            </div>
                            <div class="space-y-1">
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-700">{{ $step['competency'] ?? 'Competency' }}</span>
                                <h3 class="text-base font-bold text-slate-900">{{ $step['title'] }}</h3>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $step['rationale'] }}</p>

                                @if ($book)
                                    <div class="mt-3 flex items-center gap-2 text-xs font-semibold text-[#0094af]">
                                        <span>📖 Required Book:</span>
                                        <a href="{{ route('bookintelligence.books.show', $book->id) }}" class="underline">{{ $book->title }} ({{ $book->author?->name }})</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex-shrink-0 pt-2 sm:pt-0">
                            @if ($isCompleted)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                    ✓ {{ __('Completed') }}
                                </span>
                            @else
                                <form method="POST" action="{{ route('bookintelligence.learning.step', $learningPath->id) }}">
                                    @csrf
                                    <input type="hidden" name="step_index" value="{{ $idx }}">
                                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 shadow-sm">
                                        {{ __('Mark Step Done') }} &rarr;
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
