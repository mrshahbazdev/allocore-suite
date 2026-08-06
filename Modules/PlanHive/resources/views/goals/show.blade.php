@extends('layouts.shell')

@section('title', $goal->title)
@section('page-title', $goal->title)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <a href="{{ route('planhive.goals.index', $goal->project) }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ __('Goals') }}</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $goal->title }}</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('planhive.goals.edit', $goal) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('planhive.goals.destroy', $goal) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>

        @php($statusClass = match($goal->status) { 'achieved' => 'bg-emerald-100 text-emerald-700', 'dropped' => 'bg-rose-100 text-rose-700', default => 'bg-indigo-100 text-indigo-700' })
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize {{ $statusClass }}">{{ __($goal->status) }}</span>
                <span class="text-sm font-semibold text-slate-700">{{ $goal->progress }}%</span>
            </div>
            <div class="mt-3 h-2 w-full rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-indigo-600" style="width: {{ $goal->progress }}%"></div>
            </div>
            @if ($goal->target_date)
                <div class="mt-4 text-sm text-slate-500">{{ __('Target Date') }}: {{ $goal->target_date->format('M d, Y') }}</div>
            @endif
            @if ($goal->description)
                <p class="mt-4 whitespace-pre-line text-slate-700">{{ $goal->description }}</p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Linked Tasks') }}</h2>
            @php($goal->load('tasks.assignee'))
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($goal->tasks as $task)
                    @php($statusClass = match($task->status) { 'done' => 'bg-emerald-100 text-emerald-700', 'in_progress' => 'bg-indigo-100 text-indigo-700', 'cancelled' => 'bg-rose-100 text-rose-700', default => 'bg-slate-100 text-slate-600' })
                    <li class="flex items-center justify-between rounded-lg border border-slate-200 p-2">
                        <a href="{{ route('planhive.tasks.show', $task) }}" class="text-indigo-600 hover:underline">{{ $task->title }}</a>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold capitalize {{ $statusClass }}">{{ __($task->status) }}</span>
                            <span class="text-xs text-slate-500">{{ $task->assignee?->name ?? '-' }}</span>
                        </div>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No linked tasks.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
