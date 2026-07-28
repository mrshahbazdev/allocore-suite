@extends('layouts.shell')

@section('title', __('LoopEngine'))
@section('page-title', __('LoopEngine'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('LoopEngine') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Dashboard') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Decision-loop SOP builder with execution and audit trail.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('loopengine.processes.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('New Process') }}</a>
                <a href="{{ route('loopengine.templates.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Templates') }}</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('loopengine.processes.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Active Processes') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $processes }}</div>
            </a>
            <a href="{{ route('loopengine.runs.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Total Runs') }}</div><svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $runs }}</div>
            </a>
            <a href="{{ route('loopengine.runs.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Completed') }}</div><svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $completed }}</div>
            </a>
            <a href="{{ route('loopengine.team.assignments') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Pending Assignments') }}</div><svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m-6 3.75l3 3m0 0l3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 012.25 2.25v7.5A2.25 2.25 0 0120.25 21h-7.5A2.25 2.25 0 0110.5 18.75v-7.5a2.25 2.25 0 012.25-2.25h.75m-6 3.75l3 3m0 0l3-3m-3 3V1.5"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $pendingAssignments }}</div>
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('loopengine.templates.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Templates') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg></div>
                <div class="mt-1 text-sm text-slate-600">{{ __('Browse and install') }}</div>
            </a>
            <a href="{{ route('loopengine.webhooks.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Webhooks') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5h10.25M3.75 10.5h10.25m-10.25-3h10.25m-10.25 9.75h10.25m4.5-9.75h.008v.008H18.75V6.75zm0 3.75h.008v.008H18.75V10.5zm0 3.75h.008v.008H18.75V14.25zm0 3.75h.008v.008H18.75V18z"/></svg></div>
                <div class="mt-1 text-sm text-slate-600">{{ __('Integrations & logs') }}</div>
            </a>
            <a href="{{ route('loopengine.team.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Team') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.637-2.911M15 19.128V13.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 13.5v3.75m-3-1.837a6.375 6.375 0 0111.637-2.911"/></svg></div>
                <div class="mt-1 text-sm text-slate-600">{{ __('Members & workload') }}</div>
            </a>
            <a href="{{ route('loopengine.team.assign.create') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Assign') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m-6 3.75l3 3m0 0l3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 012.25 2.25v7.5A2.25 2.25 0 0120.25 21h-7.5A2.25 2.25 0 0110.5 18.75v-7.5a2.25 2.25 0 012.25-2.25h.75m-6 3.75l3 3m0 0l3-3m-3 3V1.5"/></svg></div>
                <div class="mt-1 text-sm text-slate-600">{{ __('New assignment') }}</div>
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Runs') }}</h2>
                    <a href="{{ route('loopengine.runs.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ __('View all') }}</a>
                </div>
                <ul class="space-y-3 text-sm">
                    @forelse ($recentRuns as $run)
                        @php($runClass = match($run->status) {
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'paused' => 'bg-amber-100 text-amber-700',
                            'cancelled' => 'bg-rose-100 text-rose-700',
                            default => 'bg-indigo-100 text-indigo-700',
                        })
                        <li class="flex items-center justify-between rounded-lg border border-slate-100 p-3 hover:bg-slate-50">
                            <div class="min-w-0">
                                <a href="{{ route('loopengine.runs.show', $run) }}" class="truncate font-medium text-indigo-600 hover:text-indigo-500">{{ $run->process->localizedName() }}</a>
                                <div class="text-xs text-slate-500">{{ $run->started_at?->format('M d, Y H:i') }}</div>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $runClass }}">{{ __($run->status) }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">{{ __('No runs yet.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Runs Last 30 Days') }}</h2>
                @if ($runsByDay->isNotEmpty())
                    <div class="mt-4 flex items-end gap-1">
                        @foreach ($runsByDay as $day => $count)
                            <div class="flex-1 rounded bg-indigo-500" style="height: {{ max(10, $count * 20) }}px" title="{{ $day }}: {{ $count }}"></div>
                        @endforeach
                    </div>
                    <div class="mt-2 flex justify-between text-xs text-slate-500">
                        <span>{{ $runsByDay->keys()->first() }}</span>
                        <span>{{ $runsByDay->keys()->last() }}</span>
                    </div>
                @else
                    <div class="mt-4 rounded-lg border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                        {{ __('No runs in the last 30 days.') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
