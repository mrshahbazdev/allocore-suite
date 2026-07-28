@extends('layouts.shell', ['title' => __('FocusMatrix Dashboard')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('FocusMatrix') }}</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Dashboard') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Capture, triage, delegate and focus on what matters.') }}</p>
        </div>
        <form method="POST" action="{{ route('focusmatrix.tasks.store') }}" class="flex flex-wrap gap-2">
            @csrf
            <input type="text" name="title" placeholder="{{ __('Quick capture...') }}" class="rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Add') }}</button>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('focusmatrix.tasks.index', ['status' => 'keep']) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
            <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Focus Score') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg></div>
            <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['focus_score'] }}%</div>
        </a>
        <a href="{{ route('focusmatrix.tasks.index', ['status' => 'keep']) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-300">
            <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Kept') }}</div><svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['kept'] }}</div>
        </a>
        <a href="{{ route('focusmatrix.delegations.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-amber-300">
            <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Delegated') }}</div><svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m-6 3.75l3 3m0 0l3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 012.25 2.25v7.5A2.25 2.25 0 0120.25 21h-7.5A2.25 2.25 0 0110.5 18.75v-7.5a2.25 2.25 0 012.25-2.25h.75m-6 3.75l3 3m0 0l3-3m-3 3V1.5"/></svg></div>
            <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['delegated'] }}</div>
        </a>
        <a href="{{ route('focusmatrix.kill-list.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-400">
            <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Dropped') }}</div><svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0V4.5A2.25 2.25 0 0013.5 2.25h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg></div>
            <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['dropped'] }}</div>
        </a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('focusmatrix.tasks.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
            <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Tasks') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg></div>
            <div class="mt-1 text-sm text-slate-600">{{ __('Triage & manage') }}</div>
        </a>
        <a href="{{ route('focusmatrix.calendar.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
            <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Calendar') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg></div>
            <div class="mt-1 text-sm text-slate-600">{{ __('Focus blocks & events') }}</div>
        </a>
        <a href="{{ route('focusmatrix.self-check.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
            <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Self Check') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg></div>
            <div class="mt-1 text-sm text-slate-600">{{ __('Weekly reflection') }}</div>
        </a>
        <a href="{{ route('focusmatrix.integrations.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
            <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Integrations') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5h10.25M3.75 10.5h10.25m-10.25-3h10.25m-10.25 9.75h10.25m4.5-9.75h.008v.008H18.75V6.75zm0 3.75h.008v.008H18.75V10.5zm0 3.75h.008v.008H18.75V14.25zm0 3.75h.008v.008H18.75V18z"/></svg></div>
            <div class="mt-1 text-sm text-slate-600">{{ __('Google, Slack, ICS') }}</div>
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Tasks') }}</h2>
                <a href="{{ route('focusmatrix.tasks.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ __('View all') }}</a>
            </div>
            @if ($recent_tasks->isEmpty())
                <div class="rounded-lg border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                    {{ __('No tasks yet. Capture one above.') }}
                </div>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($recent_tasks as $task)
                        @php($statusClass = match($task->status) {
                            'keep' => 'bg-emerald-100 text-emerald-700',
                            'delegate' => 'bg-amber-100 text-amber-700',
                            'drop' => 'bg-slate-100 text-slate-700',
                            'done' => 'bg-indigo-100 text-indigo-700',
                            default => 'bg-slate-100 text-slate-700',
                        })
                        <li class="flex items-center justify-between py-3">
                            <div class="min-w-0">
                                <a href="{{ route('focusmatrix.tasks.show', $task) }}" class="truncate font-medium text-slate-900 hover:text-indigo-600">{{ $task->title }}</a>
                                <p class="truncate text-sm text-slate-500">{{ $task->description }}</p>
                            </div>
                            <span class="ml-3 shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ __($task->status) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Quick Capture') }}</h2>
            <p class="text-sm text-slate-500">{{ __('Drop a task into your inbox to triage later.') }}</p>
            <form method="POST" action="{{ route('focusmatrix.tasks.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                    <input type="text" name="title" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Capture to Inbox') }}</button>
            </form>
        </div>
    </div>

    @if ($upcoming_delegations->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Open Delegations') }}</h2>
            <ul class="divide-y divide-slate-100">
                @foreach ($upcoming_delegations as $delegation)
                    @php($delegationClass = match($delegation->status) {
                        'accepted' => 'bg-emerald-100 text-emerald-700',
                        'declined' => 'bg-rose-100 text-rose-700',
                        'pending' => 'bg-amber-100 text-amber-700',
                        default => 'bg-slate-100 text-slate-700',
                    })
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <div class="font-medium text-slate-900">{{ $delegation->task?->title }}</div>
                            <div class="text-sm text-slate-500">{{ $delegation->delegateUser?->name ?? $delegation->delegate_name_fallback }} — {{ $delegation->deadline?->format('Y-m-d') }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $delegationClass }}">{{ __($delegation->status) }}</span>
                            <a href="{{ route('focusmatrix.delegations.show', $delegation) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ __('View') }}</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
