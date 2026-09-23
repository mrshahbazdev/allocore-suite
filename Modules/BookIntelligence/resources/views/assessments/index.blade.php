@php $title = __('Skill Assessment Engine'); @endphp
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
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 13 — Skill Assessment Engine') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('Knowledge Tests & Scenario Evaluations') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    {{ __('Evaluate team comprehension and practical execution through AI-generated scenario tests that measure strengths and pinpoint knowledge gaps.') }}
                </p>
            </div>
        </div>

        <!-- Assessments Grid -->
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($books as $book)
                @php $existingAssessment = $assessments->firstWhere('book_id', $book->id); @endphp
                <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                    <div class="space-y-3">
                        <span class="rounded bg-purple-50 px-2 py-0.5 text-[10px] font-bold uppercase text-purple-700">{{ $book->mainTopic?->name ?? 'Book Assessment' }}</span>
                        <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $book->title }}</h3>
                        <p class="text-xs text-slate-500">{{ __('By') }} {{ $book->author?->name }}</p>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ Str::limit($book->description, 120) }}</p>
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-4">
                        @if ($existingAssessment)
                            <a href="{{ route('bookintelligence.assessments.show', $existingAssessment->id) }}" class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-900 py-2.5 text-xs font-bold text-white hover:bg-slate-800 shadow-sm">
                                ✍️ {{ __('Take Assessment (5 Scenarios)') }} &rarr;
                            </a>
                        @else
                            <form method="POST" action="{{ route('bookintelligence.assessments.generate', $book->id) }}">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#ff9200] py-2.5 text-xs font-bold text-white hover:bg-orange-600 shadow-sm">
                                    ⚡ {{ __('Generate AI Assessment') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
