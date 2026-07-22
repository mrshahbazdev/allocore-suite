@extends('layouts.shell')

@section('title', $problem->title)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $problem->title }}</h1>
                <p class="text-sm text-slate-500">{{ $problem->description }}</p>
                <div class="mt-2 text-xs text-slate-500">{{ $problem->kpiDefinition?->localizedName() }} — {{ $problem->severity }} — {{ $problem->status }}</div>
            </div>
            <a href="{{ route('smartkpi.problems.actions.create', $problem) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Action') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Actions') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($problem->actions as $action)
                    <li><a href="{{ route('smartkpi.actions.edit', $action) }}" class="text-indigo-600">{{ $action->title }}</a> — {{ $action->status }} {{ $action->assignee?->name }}</li>
                @empty
                    <li class="text-slate-500">{{ __('No actions.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
