@extends('layouts.shell')

@section('title', __('LoopEngine'))
@section('page-title', __('LoopEngine Dashboard'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('LoopEngine') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Decision loop SOP builder with execution and audit trail.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('loopengine.processes.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Process') }}</a>
                <a href="{{ route('loopengine.templates.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Templates') }}</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('loopengine.processes.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                <div class="text-xs uppercase text-slate-500">{{ __('Active Processes') }}</div>
                <div class="text-2xl font-bold">{{ $processes }}</div>
            </a>
            <a href="{{ route('loopengine.runs.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                <div class="text-xs uppercase text-slate-500">{{ __('Runs') }}</div>
                <div class="text-2xl font-bold">{{ $runs }}</div>
            </a>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase text-slate-500">{{ __('Completed') }}</div>
                <div class="text-2xl font-bold">{{ $completed }}</div>
            </div>
            <a href="{{ route('loopengine.team.assignments') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                <div class="text-xs uppercase text-slate-500">{{ __('Pending Assignments') }}</div>
                <div class="text-2xl font-bold">{{ $pendingAssignments }}</div>
            </a>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('loopengine.processes.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Processes') }}</a>
            <a href="{{ route('loopengine.runs.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Runs') }}</a>
            <a href="{{ route('loopengine.team.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Team') }}</a>
            <a href="{{ route('loopengine.webhooks.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Webhooks') }}</a>
            <a href="{{ route('loopengine.export.runs.csv') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Export Runs CSV') }}</a>
            <a href="{{ route('loopengine.export.logs.csv') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Export Logs CSV') }}</a>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Runs') }}</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($recentRuns as $run)
                        <li class="flex justify-between"><a href="{{ route('loopengine.runs.show', $run) }}" class="text-indigo-600">{{ $run->process->localizedName() }}</a><span class="text-slate-500">{{ __($run->status) }}</span></li>
                    @empty
                        <li class="text-slate-500">{{ __('No runs yet.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Runs Last 30 Days') }}</h2>
                <div class="mt-4 flex items-end gap-1">
                    @foreach ($runsByDay as $day => $count)
                        <div class="flex-1 rounded bg-indigo-500" style="height: {{ max(10, $count * 20) }}px" title="{{ $day }}: {{ $count }}"></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
