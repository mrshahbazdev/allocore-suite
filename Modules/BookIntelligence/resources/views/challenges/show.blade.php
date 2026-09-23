@php $title = $challenge->title; @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="mx-auto max-w-4xl space-y-6" data-no-navigate>
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <a href="{{ route('bookintelligence.challenges.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; {{ __('Back to Practical Challenges') }}
        </a>

        <!-- Challenge Brief Card -->
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <span class="rounded bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900">
                    ⚡ {{ __('Simulation Brief') }} • {{ ucfirst($challenge->difficulty) }}
                </span>
                <span class="text-xs font-semibold text-slate-500">
                    📁 {{ __('Deliverable:') }} {{ $challenge->deliverable_format }}
                </span>
            </div>

            <h1 class="text-3xl font-extrabold text-slate-900 leading-tight">{{ $challenge->title }}</h1>

            <!-- Scenario -->
            <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-5 space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900">{{ __('The Scenario') }}</h3>
                <p class="text-xs text-slate-700 leading-relaxed">{{ $challenge->scenario_description }}</p>
            </div>

            <!-- Assignment Brief -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Your Objective & Deliverable Requirements') }}</h3>
                <div class="prose prose-slate max-w-none text-xs text-slate-800 leading-relaxed whitespace-pre-line">
                    {{ $challenge->assignment_brief }}
                </div>
            </div>
        </div>

        @if ($latestSubmission)
            <!-- Graded Submission Feedback Card -->
            <div class="rounded-3xl border {{ $latestSubmission->status === 'graded' ? 'border-emerald-200 bg-emerald-50/40' : 'border-amber-200 bg-amber-50/40' }} p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">{{ __('Executive Evaluation & Feedback') }}</h3>
                    <span class="rounded px-2.5 py-1 text-xs font-bold {{ $latestSubmission->status === 'graded' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        Score: {{ $latestSubmission->grade_score ?? 80 }}% ({{ ucfirst($latestSubmission->status) }})
                    </span>
                </div>
                <p class="text-xs text-slate-700 leading-relaxed">{{ $latestSubmission->evaluator_feedback }}</p>
            </div>
        @endif

        <!-- Submission Form -->
        <form method="POST" action="{{ route('bookintelligence.challenges.submit', $challenge->id) }}" class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm space-y-6">
            @csrf
            <div>
                <label for="submission_text" class="block text-sm font-bold text-slate-900">{{ __('Your Written Strategy / Implementation Memo') }} *</label>
                <p class="text-xs text-slate-500 mb-2">{{ __('Draft your structured solution, applying the book\'s frameworks and metrics.') }}</p>
                <textarea id="submission_text" name="submission_text" rows="10" required placeholder="Executive Summary, Framework Application, Step-by-Step Action Plan, KPI Targets..." class="w-full rounded-2xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('submission_text', $latestSubmission?->submission_text) }}</textarea>
            </div>

            <!-- Reflection Questions -->
            @if (!empty($challenge->reflection_questions))
                <div class="space-y-4 border-t border-slate-100 pt-5">
                    <h3 class="text-sm font-bold text-slate-900">{{ __('Strategic Self-Reflection') }}</h3>
                    @foreach ($challenge->reflection_questions as $rIdx => $rQuestion)
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ $rQuestion }}</label>
                            <input type="text" name="reflection_answers[{{ $rIdx }}]" placeholder="Your key reflection..." class="w-full rounded-xl border-slate-300 text-xs focus:border-[#ff9200] focus:ring-[#ff9200]">
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="submit" class="rounded-2xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-8 py-3.5 text-sm font-bold text-white shadow-md hover:from-orange-600 hover:to-orange-700">
                    🚀 {{ __('Submit Challenge for AI Executive Evaluation') }} &rarr;
                </button>
            </div>
        </form>
    </div>
@endsection
