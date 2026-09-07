@php $title = $role->name; @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="mx-auto max-w-5xl space-y-6" data-no-navigate>
        <a href="{{ route('bookintelligence.competency.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; {{ __('Back to Competency Framework') }}
        </a>

        <!-- Role Header Card -->
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <span class="rounded-md bg-blue-50 px-2.5 py-0.5 text-xs font-bold uppercase text-blue-700">{{ $role->department }}</span>
                <span class="rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-bold uppercase text-slate-700">{{ $role->level }} Level</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900">{{ $role->name }}</h1>
            <p class="text-sm text-slate-600 leading-relaxed">{{ $role->description }}</p>

            @if ($role->nextRole)
                <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 text-xs">
                    <span class="font-bold text-amber-900">{{ __('Next Career Progression Level:') }}</span>
                    <strong class="text-slate-900 font-bold ml-1">{{ $role->nextRole->name }}</strong>
                    <span class="text-slate-500">({{ $role->nextRole->level }})</span>
                </div>
            @endif
        </div>

        <!-- 3 Pillars of Competency -->
        <div class="grid gap-6 md:grid-cols-3">
            <!-- Pillar 1: Required Knowledge -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>📚</span> {{ __('Required Knowledge') }}
                </h3>
                <ul class="space-y-2 text-xs text-slate-700">
                    @forelse ($role->required_knowledge ?? [] as $k)
                        <li class="rounded-lg bg-slate-50 p-2.5 font-medium border border-slate-100">{{ $k }}</li>
                    @empty
                        <li class="text-slate-400">{{ __('None listed.') }}</li>
                    @endforelse
                </ul>
            </div>

            <!-- Pillar 2: Core Competencies -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>🎯</span> {{ __('Core Competencies') }}
                </h3>
                <ul class="space-y-2 text-xs text-slate-700">
                    @forelse ($role->required_competencies ?? [] as $c)
                        <li class="rounded-lg bg-emerald-50 p-2.5 font-medium border border-emerald-100 text-emerald-900">{{ $c }}</li>
                    @empty
                        <li class="text-slate-400">{{ __('None listed.') }}</li>
                    @endforelse
                </ul>
            </div>

            <!-- Pillar 3: Tactical Skills -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>🛠️</span> {{ __('Tactical Skills') }}
                </h3>
                <ul class="space-y-2 text-xs text-slate-700">
                    @forelse ($role->required_skills ?? [] as $s)
                        <li class="rounded-lg bg-blue-50 p-2.5 font-medium border border-blue-100 text-blue-900">{{ $s }}</li>
                    @empty
                        <li class="text-slate-400">{{ __('None listed.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
