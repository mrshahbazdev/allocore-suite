@extends('layouts.shell')

@section('title', __('Goals'))
@section('page-title', $project->name.' — '.__('Goals'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <a href="{{ route('planhive.projects.show', $project) }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ $project->name }}</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ __('Goals') }}</h1>
            </div>
            <a href="{{ route('planhive.goals.create', $project) }}" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Goal') }}</a>
        </div>

        <div class="space-y-4">
            @forelse ($goals as $goal)
                @php($statusClass = match($goal->status) { 'achieved' => 'bg-emerald-100 text-emerald-700', 'dropped' => 'bg-rose-100 text-rose-700', default => 'bg-indigo-100 text-indigo-700' })
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                        <div>
                            <a href="{{ route('planhive.goals.show', $goal) }}" class="font-semibold text-slate-900 hover:text-indigo-600">{{ $goal->title }}</a>
                            <div class="mt-1 text-xs text-slate-500">{{ $goal->target_date?->format('M d') ?? __('No target date') }} — <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium capitalize {{ $statusClass }}">{{ __($goal->status) }}</span></div>
                        </div>
                        <div class="text-sm font-semibold text-slate-700">{{ $goal->progress }}%</div>
                    </div>
                    <div class="mt-3 h-2 w-full rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-indigo-600" style="width: {{ $goal->progress }}%"></div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">{{ __('No goals yet.') }}</div>
            @endforelse
        </div>

        <div>{{ $goals->links() }}</div>
    </div>
@endsection
