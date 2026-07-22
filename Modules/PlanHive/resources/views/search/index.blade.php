@extends('layouts.shell')

@section('title', __('Search'))
@section('page-title', __('Search'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Search') }}</h1>
        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex gap-3">
            <input type="text" name="q" value="{{ $term }}" placeholder="{{ __('Search projects, tasks, contacts...') }}" class="w-full rounded-lg border-slate-300">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        @if ($term)
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Projects') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @forelse ($projects as $project)
                            <li><a href="{{ route('planhive.projects.show', $project) }}" class="text-indigo-600">{{ $project->name }}</a></li>
                        @empty
                            <li class="text-slate-500">{{ __('No results.') }}</li>
                        @endforelse
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Tasks') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @forelse ($tasks as $task)
                            <li><a href="{{ route('planhive.projects.show', $task->project) }}" class="text-indigo-600">{{ $task->title }}</a> <span class="text-slate-500">({{ $task->project->name }})</span></li>
                        @empty
                            <li class="text-slate-500">{{ __('No results.') }}</li>
                        @endforelse
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Contacts') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @forelse ($contacts as $contact)
                            <li><a href="{{ route('planhive.contacts.edit', $contact) }}" class="text-indigo-600">{{ $contact->name }}</a> <span class="text-slate-500">({{ $contact->company ?? '-' }})</span></li>
                        @empty
                            <li class="text-slate-500">{{ __('No results.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endsection
