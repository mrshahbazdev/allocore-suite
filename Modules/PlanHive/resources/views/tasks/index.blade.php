@extends('layouts.shell')

@section('title', __('Tasks'))
@section('page-title', $project->name.' — '.__('Tasks'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <a href="{{ route('planhive.projects.show', $project) }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ $project->name }}</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ __('Tasks') }}</h1>
            </div>
            <a href="{{ route('planhive.tasks.create', $project) }}" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Task') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($tasks as $task)
                @php($statusClass = match($task->status) { 'done' => 'bg-emerald-100 text-emerald-700', 'in_progress' => 'bg-indigo-100 text-indigo-700', 'cancelled' => 'bg-rose-100 text-rose-700', default => 'bg-slate-100 text-slate-600' })
                @php($priorityClass = in_array($task->priority, ['high', 'urgent'], true) ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600')
                <div class="flex flex-col gap-2 border-b border-slate-100 p-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <a href="{{ route('planhive.tasks.show', $task) }}" class="font-semibold text-slate-900 hover:text-indigo-600">{{ $task->title }}</a>
                        <div class="mt-1 text-xs text-slate-500">{{ $task->assignee?->name ?? __('Unassigned') }} — {{ $task->due_date?->format('M d') ?? __('No due date') }}</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize {{ $statusClass }}">{{ __($task->status) }}</span>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium capitalize {{ $priorityClass }}">{{ __($task->priority) }}</span>
                        <a href="{{ route('planhive.tasks.edit', $task) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500">{{ __('No tasks yet.') }}</div>
            @endforelse
        </div>

        <div>{{ $tasks->links() }}</div>
    </div>
@endsection
