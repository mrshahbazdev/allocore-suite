@extends('layouts.shell')

@section('title', $task->title)
@section('page-title', $task->title)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <a href="{{ route('planhive.tasks.index', $task->project) }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ __('Tasks') }}</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $task->title }}</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('planhive.tasks.edit', $task) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('planhive.tasks.destroy', $task) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Project') }}</div>
                    <a href="{{ route('planhive.projects.show', $task->project) }}" class="font-medium text-indigo-600 hover:underline">{{ $task->project->name }}</a>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    <div class="font-medium text-slate-900">{{ __($task->status) }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Priority') }}</div>
                    <div class="font-medium text-slate-900">{{ __($task->priority) }}</div>
                </div>
                @if ($task->goal)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Goal') }}</div>
                        <a href="{{ route('planhive.goals.show', $task->goal) }}" class="font-medium text-indigo-600 hover:underline">{{ $task->goal->title }}</a>
                    </div>
                @endif
                @if ($task->due_date)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Due Date') }}</div>
                        <div class="font-medium text-slate-900">{{ $task->due_date->format('M d, Y') }}</div>
                    </div>
                @endif
                @if ($task->assignee)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Assignee') }}</div>
                        <div class="font-medium text-slate-900">{{ $task->assignee->name }}</div>
                    </div>
                @endif
            </div>
            @if ($task->description)
                <p class="mt-6 whitespace-pre-line text-slate-700">{{ $task->description }}</p>
            @endif
        </div>
    </div>
@endsection
