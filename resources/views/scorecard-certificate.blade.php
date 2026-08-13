@extends('layouts.public')

@section('title', __('Allocore Score Certificate — :name', ['name' => $team->name]))

@section('content')
    <section class="py-12 lg:py-20">
        <div class="mx-auto max-w-4xl px-6 lg:px-8">
            <div class="rounded-2xl border-4 border-slate-100 bg-white p-10 shadow-lg sm:p-16 text-center">
                <p class="text-sm font-semibold uppercase tracking-widest text-slate-500">{{ __('Allocore Score Certificate') }}</p>
                <h1 class="mt-4 text-3xl font-bold text-slate-900 sm:text-4xl">{{ $team->name }}</h1>

                <div class="mt-10 flex flex-col items-center justify-center">
                    <div class="text-9xl font-black" style="color: {{ config('app.team_branding.primary_color') ?? '#4f46e5' }}">{{ $score->score }}</div>
                    <p class="mt-2 text-lg text-slate-500">{{ __('out of 100') }}</p>
                    <span class="mt-4 rounded-full px-4 py-1.5 text-sm font-semibold
                        {{ match($score->maturity_level) { 'Excellent' => 'bg-emerald-100 text-emerald-700', 'Strong' => 'bg-green-100 text-green-700', 'Solid' => 'bg-blue-100 text-blue-700', 'Weak' => 'bg-amber-100 text-amber-700', default => 'bg-red-100 text-red-700' } }}">
                        {{ __($score->maturity_level) }}
                    </span>
                </div>

                @if ($score->industry)
                    <div class="mt-8 text-slate-700">
                        <p class="text-lg">{{ __('Industry') }}: <span class="font-semibold">@include('partials.industry-display', ['industry' => $score->industry, 'industrySub' => $score->industry_sub])</span></p>
                        @if ($score->size)
                            <p class="text-lg">{{ __('Company size') }}: <span class="font-semibold">{{ $score->size }}</span></p>
                        @endif
                        @if ($score->company_age)
                            <p class="text-lg">{{ __('Company age') }}: <span class="font-semibold">{{ $score->company_age }} {{ __('years') }}</span></p>
                        @endif
                    </div>
                @endif

                @if ($benchmark !== null)
                    <p class="mt-6 text-lg text-slate-700">
                        {{ __('This company scores better than :percent% of all companies in the same industry.', ['percent' => $benchmark]) }}
                    </p>
                @endif

                <div class="mt-10 border-t border-slate-100 pt-8 text-sm text-slate-500">
                    <p>{{ __('Issued on') }} {{ $score->calculated_at->format('d.m.Y') }}</p>
                    <p class="mt-2">{{ __('Verified by Allocore') }} — {{ route('scorecard.public', $team->public_score_slug) }}</p>
                </div>
            </div>

            <div class="mt-8 text-center">
                <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Print / Save PDF') }}</button>
            </div>
        </div>
    </section>
@endsection
