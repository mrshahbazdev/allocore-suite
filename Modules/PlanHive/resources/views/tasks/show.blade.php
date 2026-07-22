@extends('layouts.shell')

@section('title', $task->title)
@section('page-title', $task->title)

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $task->title }}</h1>
        <div class="mt-2 text-sm text-slate-500">{{ $task->project->name }} — {{ __($task->status) }} — {{ __($task->priority) }}</div>
        <p class="mt-4 whitespace-pre-line text-slate-700">{{ $task->description }}</p>
        <div class="mt-6 flex gap-3">
            <a href="{{ route('planhive.tasks.edit', $task) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('planhive.tasks.destroy', $task) }}" class="inline">
                @csrf
                @method('DELETE')
                <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
@endsection
