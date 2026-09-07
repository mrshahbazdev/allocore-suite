@php $title = __('Personalized AI Learning Paths'); @endphp
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
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 12 — Personalized Learning Paths') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('AI-Powered Knowledge & Career Roadmaps') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    {{ __('Generate custom step-by-step reading sequences and milestone action plans based on your target career goal.') }}
                </p>
            </div>
        </div>

        <!-- Generator Box -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span>🤖</span> {{ __('Generate a New Personalized Learning Path') }}
            </h3>
            <form method="POST" action="{{ route('bookintelligence.learning.generate') }}" class="mt-4 grid gap-4 sm:grid-cols-3">
                @csrf
                <div>
                    <label for="current_role_id" class="block text-xs font-semibold text-slate-700">{{ __('Current Role') }}</label>
                    <select id="current_role_id" name="current_role_id" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="">{{ __('-- Select Current Role --') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->department }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="target_role_id" class="block text-xs font-semibold text-slate-700">{{ __('Target Career Goal Role') }} *</label>
                    <select id="target_role_id" name="target_role_id" required class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="">{{ __('-- Select Target Goal --') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->department }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-xl bg-[#ff9200] py-2.5 text-sm font-bold text-white shadow-sm hover:bg-orange-600">
                        ⚡ {{ __('Generate Learning Path') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- My Learning Paths List -->
        <h2 class="text-xl font-bold text-slate-900 pt-2">{{ __('My Active Learning Paths') }}</h2>
        @if ($learningPaths->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm text-sm text-slate-500">
                <p>{{ __('You have not generated a personalized learning path yet.') }}</p>
                <p class="mt-1 text-slate-400">{{ __('Select your target role above to generate your customized AI knowledge roadmap.') }}</p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($learningPaths as $path)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="rounded bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700">{{ $path->targetRole?->name ?? 'Career Path' }}</span>
                            <span class="rounded px-2 py-0.5 text-xs font-bold uppercase {{ $path->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $path->status }}</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">{{ $path->title }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ Str::limit($path->ai_rationale, 150) }}</p>

                        <!-- Progress Bar -->
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                                <span>{{ __('Roadmap Progress') }}</span>
                                <span class="text-[#ff9200]">{{ $path->progress_percent }}%</span>
                            </div>
                            <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-[#ff9200]" style="width: {{ $path->progress_percent }}%"></div>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-3 flex items-center justify-between text-xs">
                            <span class="text-slate-400">{{ count($path->steps ?? []) }} {{ __('milestone steps') }}</span>
                            <a href="{{ route('bookintelligence.learning.show', $path->id) }}" class="font-bold text-[#0094af] hover:underline">
                                {{ __('Continue Learning Path') }} &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
