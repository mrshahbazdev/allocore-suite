@extends('layouts.public')

@section('title', __(':name — Allocore Score', ['name' => $team->name]))
@section('meta_description', __('Public Allocore Score for :name', ['name' => $team->name]))
@section('og_title', __(':name — Allocore Score :score', ['name' => $team->name, 'score' => $score->score]))
@section('og_description', __('Maturity: :maturity', ['maturity' => $score->maturity_level]))

@section('content')
    <section class="py-16 lg:py-24">
        <div class="mx-auto max-w-5xl px-6 lg:px-8 text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('Allocore Score') }}</p>
            <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl">{{ $team->name }}</h1>

            <div class="mt-10 flex flex-col items-center justify-center gap-4">
                <div class="text-8xl font-black text-slate-900">{{ $score->score }}</div>
                <span class="rounded-full px-4 py-1.5 text-sm font-semibold
                    {{ match($score->maturity_level) { 'Excellent' => 'bg-emerald-100 text-emerald-700', 'Strong' => 'bg-green-100 text-green-700', 'Solid' => 'bg-blue-100 text-blue-700', 'Weak' => 'bg-amber-100 text-amber-700', default => 'bg-red-100 text-red-700' } }}">
                    {{ $score->maturity_level }}
                </span>
                <p class="text-sm text-slate-500">{{ __('out of 100') }} &middot; {{ $score->calculated_at->diffForHumans() }}</p>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-white py-16">
        <div class="mx-auto max-w-5xl px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-slate-900">{{ __('Pillar breakdown') }}</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($score->pillars as $pillar)
                    <div class="rounded-xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-900">{{ $pillar['name'] }}</span>
                            <span class="text-2xl font-bold text-slate-900">{{ $pillar['score'] }}</span>
                        </div>
                        <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full" style="width: {{ min(100, max(0, $pillar['score'])) }}%; background-color: {{ config('app.team_branding.primary_color') ?? '#4f46e5' }}"></div>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">{{ $pillar['maturity'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-12 text-center">
        <a href="{{ route('home') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">{{ __('Powered by Allocore') }}</a>
    </section>
@endsection
