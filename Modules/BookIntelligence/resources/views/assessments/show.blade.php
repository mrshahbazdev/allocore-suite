@php $title = $assessment->title; @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="mx-auto max-w-4xl space-y-6" data-no-navigate>
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <a href="{{ route('bookintelligence.assessments.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; {{ __('Back to Assessments') }}
        </a>

        <!-- Header Card -->
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="rounded bg-purple-50 px-2.5 py-1 text-xs font-bold text-purple-700">
                    ⏱️ {{ $assessment->time_limit_minutes ?? 15 }} {{ __('Minutes') }} • {{ __('Pass score:') }} {{ $assessment->passing_score }}%
                </span>
                @if ($latestSubmission)
                    <span class="rounded px-3 py-1 text-xs font-extrabold {{ $latestSubmission->passed ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                        {{ $latestSubmission->passed ? '✓ PASSED' : '✗ NEEDS IMPROVEMENT' }} ({{ $latestSubmission->score }}%)
                    </span>
                @endif
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900">{{ $assessment->title }}</h1>
            <p class="text-sm text-slate-600">{{ $assessment->description }}</p>
        </div>

        @if ($latestSubmission)
            <!-- Results Feedback Card -->
            <div class="rounded-3xl border {{ $latestSubmission->passed ? 'border-emerald-200 bg-emerald-50/40' : 'border-rose-200 bg-rose-50/40' }} p-6 space-y-4">
                <h3 class="text-base font-bold text-slate-900">{{ __('Latest Assessment Feedback & Competency Radar') }}</h3>
                <p class="text-xs text-slate-700">{{ $latestSubmission->feedback }}</p>

                <!-- Strengths -->
                @if (!empty($latestSubmission->strengths))
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-emerald-800">{{ __('Verified Strengths:') }}</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($latestSubmission->strengths as $s)
                                <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-900">✓ {{ $s }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Gaps -->
                @if (!empty($latestSubmission->gaps))
                    <div class="space-y-2 pt-2 border-t border-rose-200/60">
                        <span class="text-xs font-bold text-rose-800">{{ __('Identified Knowledge Gaps & Explanations:') }}</span>
                        @foreach ($latestSubmission->gaps as $gap)
                            <div class="rounded-xl bg-white p-3.5 border border-rose-100 text-xs space-y-1">
                                <p class="font-bold text-slate-900">❓ {{ $gap['question'] }}</p>
                                <p class="text-rose-700">💡 <strong>Correction:</strong> {{ $gap['explanation'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Assessment Quiz Form -->
        <form method="POST" action="{{ route('bookintelligence.assessments.submit', $assessment->id) }}" class="space-y-6">
            @csrf
            @foreach ($assessment->questions ?? [] as $qIndex => $q)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">
                            {{ $qIndex + 1 }}
                        </span>
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ $q['competency'] ?? 'Core Knowledge' }}</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $q['question'] }}</h3>

                    <div class="space-y-2.5 pt-2">
                        @foreach ($q['options'] ?? [] as $optIndex => $option)
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3.5 hover:bg-slate-50 cursor-pointer transition">
                                <input type="radio" name="answers[{{ $qIndex }}]" value="{{ $optIndex }}" required class="mt-0.5 text-[#ff9200] focus:ring-[#ff9200]">
                                <span class="text-xs text-slate-800 leading-relaxed">{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end pt-4">
                <button type="submit" class="rounded-2xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-8 py-3.5 text-sm font-bold text-white shadow-md hover:from-orange-600 hover:to-orange-700">
                    🚀 {{ __('Submit Assessment for Grading') }} &rarr;
                </button>
            </div>
        </form>
    </div>
@endsection
