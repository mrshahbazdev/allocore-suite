@extends('layouts.shell')

@section('title', __('Projects'))
@section('page-title', __('Projects'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Projects') }}</h1>
            <a href="{{ route('planhive.projects.create') }}" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Project') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Search') }}</label><input type="text" name="search" value="{{ request('search') }}" class="mt-1 rounded-lg border-slate-300"></div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 rounded-lg border-slate-300">
                        <option value="">{{ __('All') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>{{ __('Archived') }}</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    </select>
                </div>
                <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($projects as $project)
                <a href="{{ route('planhive.projects.show', $project) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full" style="background-color: {{ $project->color }}"></span>
                        <span class="font-semibold text-slate-900">{{ $project->name }}</span>
                    </div>
                    <p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ $project->description }}</p>
                    <div class="mt-3 text-xs text-slate-500">{{ __($project->status) }} — {{ $project->start_date?->format('M d') ?? '-' }} / {{ $project->end_date?->format('M d') ?? '-' }}</div>
                </a>
            @endforeach
        </div>

        <div class="mt-4">{{ $projects->links() }}</div>
    </div>
@endsection
