@extends('layouts.shell', ['title' => __('Tasks')])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Tasks') }}</h1>
        <form method="POST" action="{{ route('focusmatrix.tasks.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="title" placeholder="{{ __('Quick capture...') }}" class="rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add') }}</button>
        </form>
    </div>

    <div class="flex gap-2 overflow-x-auto pb-2">
        @foreach (['inbox','keep','delegate','drop','done'] as $s)
            <a href="{{ route('focusmatrix.tasks.index', ['status' => $s]) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $status === $s ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                {{ ucfirst($s) }} ({{ $counts[$s] ?? 0 }})
            </a>
        @endforeach
    </div>

    @if ($tasks->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">
            {{ __('No tasks in this column.') }}
        </div>
    @else
        <div class="space-y-3">
            @foreach ($tasks as $task)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex items-start justify-between gap-4">
                    <div>
                        <a href="{{ route('focusmatrix.tasks.show', $task) }}" class="font-semibold text-slate-900 hover:text-indigo-600">{{ $task->title }}</a>
                        <p class="text-sm text-slate-500 line-clamp-1">{{ $task->description }}</p>
                        @if ($task->ai_suggestion)
                            <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">{{ __('AI:') }} {{ $task->ai_suggestion }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($task->status === 'inbox')
                            <a href="{{ route('focusmatrix.tasks.triage', $task) }}" class="text-xs rounded-lg bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100">{{ __('Triage') }}</a>
                        @endif
                        <form method="POST" action="{{ route('focusmatrix.tasks.destroy', $task) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-rose-600 hover:underline">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
