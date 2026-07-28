@extends('layouts.shell')

@section('title', __('PlanHive'))
@section('page-title', __('PlanHive Dashboard'))

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('PlanHive') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Dashboard') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Projects, tasks, goals & team calendar.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('planhive.calendar.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Calendar') }}</a>
                <a href="{{ route('planhive.projects.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('New Project') }}</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('planhive.projects.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Projects') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M3.75 7.5h16.5M3.75 3h16.5m-16.5 9h16.5"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['projects'] }}</div>
            </a>
            <a href="{{ route('planhive.tasks.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Tasks') }}</div><svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['tasks'] }}</div>
            </a>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Done') }}</div><svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['done'] }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Month Events') }}</div><svg class="h-5 w-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['events'] }}</div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('My Tasks') }}</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($myTasks as $task)
                        @php($statusClass = match($task->status) {
                            'done' => 'bg-emerald-100 text-emerald-700',
                            'in_progress' => 'bg-indigo-100 text-indigo-700',
                            'todo' => 'bg-slate-100 text-slate-600',
                            default => 'bg-slate-100 text-slate-600',
                        })
                        <li class="flex items-center justify-between rounded-lg border border-slate-100 p-3 hover:bg-slate-50 transition">
                            <div>
                                <div class="font-medium text-slate-900">{{ $task->title }}</div>
                                <div class="text-xs text-slate-500">{{ $task->project->name }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize {{ $statusClass }}">{{ __($task->status) }}</span>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $task->priority === 'high' || $task->priority === 'urgent' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($task->priority) }}</span>
                                <span class="text-xs text-slate-500">{{ $task->due_date?->format('M d') ?? '-' }}</span>
                            </div>
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
                            <li class="flex items-center justify-between rounded-lg border border-slate-100 p-2"><span class="font-medium text-slate-900">{{ $event->title }}</span><span class="text-xs text-slate-500">{{ $event->start_at->format('M d H:i') }}</span></li>
                        @empty
                            <li class="text-slate-500">{{ __('No upcoming events.') }}</li>
                        @endforelse
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Reminders') }}</h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        @forelse ($reminders as $reminder)
                            <li class="flex items-center justify-between rounded-lg border border-slate-100 p-2"><span class="font-medium text-slate-900">{{ $reminder->title }}</span><span class="text-xs text-slate-500">{{ $reminder->remind_at->format('M d H:i') }}</span></li>
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
                    <a href="{{ route('planhive.projects.show', $project) }}" class="rounded-xl border border-slate-200 p-5 transition hover:border-indigo-300 hover:bg-indigo-50/30">
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
