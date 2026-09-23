@php $title = __('Expertise Progression & Mastery Levels'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6" data-no-navigate>
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 15 — Expertise Progression System') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('Organizational Knowledge & Mastery Tiers') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    {{ __('Advance from Starter to Master through books read, passed scenario assessments, and completed practical challenges.') }}
                </p>
            </div>
        </div>

        <!-- My Profile Card -->
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-5">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-tr from-[#ff9200] to-orange-500 text-3xl text-white shadow-lg font-black">
                        {{ strtoupper(substr($profile->expertise_level, 0, 1)) }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-extrabold uppercase text-orange-800">
                                {{ $profile->expertise_level }} Level
                            </span>
                            <span class="text-xs font-bold text-slate-400">• {{ $profile->points }} Total XP Points</span>
                        </div>
                        <h2 class="text-2xl font-extrabold text-slate-900">{{ auth()->user()->name }}</h2>
                        <p class="text-xs text-slate-500">
                            {{ __('Current Focus:') }} <strong class="text-slate-800">{{ $profile->currentRole?->name ?? 'General Practitioner' }}</strong>
                            &rarr; Target: <strong class="text-[#0094af]">{{ $profile->targetRole?->name ?? 'Next Level' }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Role Target Changer -->
                <form method="POST" action="{{ route('bookintelligence.expertise.roles') }}" class="rounded-2xl border border-slate-100 bg-slate-50 p-4 space-y-2">
                    @csrf
                    <label class="block text-xs font-bold text-slate-700">{{ __('Set My Target Role:') }}</label>
                    <div class="flex items-center gap-2">
                        <select name="target_role_id" class="rounded-xl border-slate-300 text-xs font-semibold focus:border-[#ff9200] focus:ring-[#ff9200]">
                            <option value="">{{ __('-- Select Target Role --') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) $profile->target_role_id === (string) $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-800">
                            {{ __('Save') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Milestone Stats -->
            <div class="mt-8 grid grid-cols-3 gap-4 border-t border-slate-100 pt-6 text-center">
                <div>
                    <span class="text-2xl font-extrabold text-slate-900">{{ $profile->books_read_count }}</span>
                    <p class="text-xs font-semibold text-slate-500 uppercase mt-1">📖 Books Read</p>
                </div>
                <div>
                    <span class="text-2xl font-extrabold text-slate-900">{{ $profile->assessments_passed_count }}</span>
                    <p class="text-xs font-semibold text-slate-500 uppercase mt-1">✍️ Assessments Passed</p>
                </div>
                <div>
                    <span class="text-2xl font-extrabold text-slate-900">{{ $profile->challenges_completed_count }}</span>
                    <p class="text-xs font-semibold text-slate-500 uppercase mt-1">⚡ Challenges Completed</p>
                </div>
            </div>
        </div>

        <!-- 6 Mastery Tiers Ladder -->
        <h3 class="text-xl font-bold text-slate-900 pt-2">{{ __('The 6 Expertise Progression Tiers') }}</h3>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            @foreach ($levels as $lvl)
                @php $isCurrent = $profile->expertise_level === $lvl['key']; @endphp
                <div class="flex flex-col justify-between rounded-2xl border {{ $isCurrent ? 'border-[#ff9200] bg-orange-50/50 shadow-md ring-2 ring-[#ff9200]' : 'border-slate-200 bg-white' }} p-5 text-center space-y-3">
                    <div>
                        <span class="text-2xl">🏆</span>
                        <h4 class="mt-2 text-sm font-bold text-slate-900">{{ $lvl['name'] }}</h4>
                        <p class="text-[11px] font-semibold text-slate-400 mt-1">{{ $lvl['min_points'] }}+ XP</p>
                    </div>
                    @if ($isCurrent)
                        <span class="rounded-full bg-[#ff9200] px-2 py-0.5 text-[10px] font-extrabold uppercase text-white">Your Level</span>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Team Leaderboard -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-900">{{ __('Team Knowledge & Competency Leaderboard') }}</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase text-slate-400">
                        <tr>
                            <th class="p-3">Rank</th>
                            <th class="p-3">Employee</th>
                            <th class="p-3">Level</th>
                            <th class="p-3">Target Goal</th>
                            <th class="p-3">Total XP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($teamProfiles as $rIndex => $tp)
                            <tr>
                                <td class="p-3 font-extrabold text-slate-900">#{{ $rIndex + 1 }}</td>
                                <td class="p-3 font-bold text-slate-900">{{ $tp->user?->name ?? 'Teammate' }}</td>
                                <td class="p-3">
                                    <span class="rounded-full bg-orange-100 px-2.5 py-0.5 text-[10px] font-extrabold uppercase text-orange-800">
                                        {{ $tp->expertise_level }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-500">{{ $tp->targetRole?->name ?? '—' }}</td>
                                <td class="p-3 font-extrabold text-[#ff9200]">{{ $tp->points }} XP</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
