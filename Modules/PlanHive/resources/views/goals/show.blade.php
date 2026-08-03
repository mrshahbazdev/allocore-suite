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
    </div>
@endsection
