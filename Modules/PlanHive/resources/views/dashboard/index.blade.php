@extends('layouts.shell')

@section('title', __('PlanHive'))
@section('page-title', __('PlanHive Dashboard'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('PlanHive') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Projects, tasks, goals & team calendar.') }}</p>
            </div>
            <a href="{{ route('planhive.projects.create') }}" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Project') }}</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $label => $value)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase text-slate-500">{{ __(ucfirst($label)) }}</div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('My Tasks') }}</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($myTasks as $task)
                        <li class="flex items-center justify-between rounded-lg border border-slate-200 p-3">
                            <div>
                                <div class="font-medium text-slate-900">{{ $task->title }}</div>
                                <div class="text-xs text-slate-500">{{ $task->project->name }} — {{ __($task->status) }}</div>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $task->priority === 'high' || $task->priority === 'urgent' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600' }}">{{ $task->due_date?->format('M d') ?? '-' }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">{{ __('No open tasks.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Upcoming Events') }}</h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        @forelse ($upcomingEvents as $event)
                            <li class="flex items-center justify-between"><span>{{ $event->title }}</span><span class="text-xs text-slate-500">{{ $event->start_at->format('M d H:i') }}</span></li>
                        @empty
                            <li class="text-slate-500">{{ __('No upcoming events.') }}</li>
                        @endforelse
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Reminders') }}</h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        @forelse ($reminders as $reminder)
                            <li class="flex items-center justify-between"><span>{{ $reminder->title }}</span><span class="text-xs text-slate-500">{{ $reminder->remind_at->format('M d H:i') }}</span></li>
                        @empty
                            <li class="text-slate-500">{{ __('No reminders.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Projects') }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($projects as $project)
                    <a href="{{ route('planhive.projects.show', $project) }}" class="rounded-xl border border-slate-200 p-5 hover:border-indigo-300">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full" style="background-color: {{ $project->color }}"></span>
                            <span class="font-semibold text-slate-900">{{ $project->name }}</span>
                        </div>
                        <div class="mt-2 text-xs text-slate-500">{{ $project->tasks_count }} {{ __('tasks') }} — {{ $project->goals_count }} {{ __('goals') }}</div>
                    </a>
                @empty
                    <p class="text-slate-500">{{ __('No projects yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
