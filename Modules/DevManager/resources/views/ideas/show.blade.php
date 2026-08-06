@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $idea->title }}</h1>
            <p class="text-sm text-slate-500">{{ $idea->status }} &middot; {{ $idea->created_at->format('M d, Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('devmanager.ideas.edit', $idea) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Edit Idea') }}</a>
            <a href="{{ route('devmanager.requirements.create', $idea) }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('Add Requirement') }}</a>
            <a href="{{ route('devmanager.user-stories.create', $idea) }}" class="rounded-lg bg-[#0094af] px-4 py-2 text-sm font-semibold text-white hover:bg-[#007a8f]">{{ __('Add User Story') }}</a>
            <a href="{{ route('devmanager.milestones.create', $idea) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">{{ __('Add Milestone') }}</a>
            <a href="{{ route('devmanager.releases.create', $idea) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Add Release') }}</a>
        </div>
    </div>

    @if($idea->description)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Description') }}</h2>
            <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $idea->description }}</p>
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Problem') }}</h2>
            <p class="mt-2 text-sm text-slate-700">{{ $idea->problem ?: '—' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Audience') }}</h2>
            <p class="mt-2 text-sm text-slate-700">{{ $idea->audience ?: '—' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Value') }}</h2>
            <p class="mt-2 text-sm text-slate-700">{{ $idea->value ?: '—' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Cost of problem') }}</h2>
            <p class="mt-2 text-sm text-slate-700">{{ $idea->cost_of_problem ?: '—' }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Requirements') }}</h2>
            <a href="{{ route('devmanager.requirements.index', $idea) }}" class="text-sm text-[#0094af] hover:underline">{{ __('View all') }}</a>
        </div>
        @if($idea->requirements->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No requirements yet.') }}</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($idea->requirements->take(5) as $requirement)
                    <li class="flex items-center justify-between py-2"><span class="text-sm text-slate-700">{{ $requirement->title }}</span><span class="text-xs text-slate-500">{{ $requirement->status }}</span></li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('User Stories') }}</h2>
            <a href="{{ route('devmanager.user-stories.index', $idea) }}" class="text-sm text-[#0094af] hover:underline">{{ __('View all') }}</a>
        </div>
        @if($idea->userStories->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No user stories yet.') }}</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($idea->userStories->take(5) as $story)
                    <li class="py-2 text-sm text-slate-700">{{ __('As a :role, I want :action so that :benefit', ['role' => $story->role, 'action' => $story->action, 'benefit' => $story->benefit]) }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
