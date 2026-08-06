@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Roadmap') }}</h1>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Milestones') }}</h2>
            @if($milestones->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No milestones yet.') }}</p>
            @else
                <div class="space-y-3">
                    @foreach($milestones as $milestone)
                        <div class="rounded-xl border-l-4 border-emerald-500 bg-slate-50 p-3">
                            <p class="font-medium text-slate-900">{{ $milestone->title }}</p>
                            <p class="text-xs text-slate-500">{{ $milestone->idea->title }} &middot; {{ $milestone->due_date?->format('M d, Y') ?: __('No date') }} &middot; {{ $milestone->status }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Releases') }}</h2>
            @if($releases->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No releases yet.') }}</p>
            @else
                <div class="space-y-3">
                    @foreach($releases as $release)
                        <div class="rounded-xl border-l-4 border-indigo-500 bg-slate-50 p-3">
                            <p class="font-medium text-slate-900">v{{ $release->version }} {{ $release->title }}</p>
                            <p class="text-xs text-slate-500">{{ $release->idea->title }} &middot; {{ $release->released_at?->format('M d, Y') ?: __('Not released') }} &middot; {{ $release->status }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Active Ideas') }}</h2>
        @if($ideas->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No active ideas.') }}</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($ideas as $idea)
                    <li class="flex items-center justify-between py-3">
                        <a href="{{ route('devmanager.ideas.show', $idea) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $idea->title }}</a>
                        <span class="text-xs text-slate-500">{{ $idea->status }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
