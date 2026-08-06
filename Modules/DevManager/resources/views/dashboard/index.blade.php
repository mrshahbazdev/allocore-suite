@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('SaaS Development Manager') }}</h1>
        <a href="{{ route('devmanager.ideas.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('New Idea') }}</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Ideas') }}</p>
            <p class="mt-2 text-3xl font-bold text-[#0094af]">{{ $stats['ideas'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Requirements') }}</p>
            <p class="mt-2 text-3xl font-bold text-[#ff9200]">{{ $stats['requirements'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('User Stories') }}</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['user_stories'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Done Stories') }}</p>
            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['done_stories'] }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Ideas') }}</h2>
        @if($ideas->isEmpty())
            <p class="mt-4 text-sm text-slate-500">{{ __('No ideas yet.') }}</p>
        @else
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach($ideas as $idea)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <a href="{{ route('devmanager.ideas.show', $idea) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $idea->title }}</a>
                            <p class="text-xs text-slate-500">{{ $idea->status }} &middot; {{ $idea->requirements_count }} {{ __('requirements') }} &middot; {{ $idea->user_stories_count }} {{ __('stories') }}</p>
                        </div>
                        <a href="{{ route('devmanager.ideas.show', $idea) }}" class="text-sm text-[#0094af] hover:underline">{{ __('View') }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
