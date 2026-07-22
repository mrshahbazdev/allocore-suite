@extends('layouts.shell', ['title' => __('FocusMatrix Dashboard')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('FocusMatrix') }}</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Focus Score') }}</div>
            <div class="text-3xl font-bold text-indigo-600">{{ $stats['focus_score'] }}%</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Kept') }}</div>
            <div class="text-3xl font-bold text-emerald-600">{{ $stats['kept'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Delegated') }}</div>
            <div class="text-3xl font-bold text-amber-600">{{ $stats['delegated'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Dropped') }}</div>
            <div class="text-3xl font-bold text-slate-600">{{ $stats['dropped'] }}</div>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Tasks') }}</h2>
                <a href="{{ route('focusmatrix.tasks.index') }}" class="text-sm text-indigo-600 hover:underline">{{ __('View all') }}</a>
            </div>
            @if ($recent_tasks->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No tasks yet. Capture one below.') }}</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($recent_tasks as $task)
                        <li class="py-3 flex items-center justify-between">
                            <a href="{{ route('focusmatrix.tasks.show', $task) }}" class="font-medium text-slate-800 hover:text-indigo-600">{{ $task->title }}</a>
                            <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-600">{{ $task->status }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Quick Capture') }}</h2>
            <form method="POST" action="{{ route('focusmatrix.tasks.store') }}">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                    <input type="text" name="title" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div class="mt-3">
                    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"></textarea>
                </div>
                <button class="mt-4 w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Capture to Inbox') }}</button>
            </form>
        </div>
    </div>

    @if ($upcoming_delegations->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Open Delegations') }}</h2>
            <ul class="divide-y divide-slate-100">
                @foreach ($upcoming_delegations as $delegation)
                    <li class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-slate-800">{{ $delegation->task?->title }}</div>
                            <div class="text-sm text-slate-500">{{ $delegation->delegateUser?->name ?? $delegation->delegate_name_fallback }} — {{ $delegation->deadline?->format('Y-m-d') }}</div>
                        </div>
                        <a href="{{ route('focusmatrix.delegations.show', $delegation) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
