@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('devmanager.ideas.show', $idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ $idea->title }}</a>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Milestones') }}</h1>
        </div>
        <a href="{{ route('devmanager.milestones.create', $idea) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">{{ __('Add Milestone') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($milestones->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No milestones yet.') }}</p>
        @else
            <div class="space-y-4">
                @foreach($milestones as $milestone)
                    <div class="flex items-start justify-between rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div>
                            <a href="{{ route('devmanager.milestones.edit', $milestone) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $milestone->title }}</a>
                            <p class="text-xs text-slate-500">{{ $milestone->due_date?->format('M d, Y') ?: __('No due date') }} &middot; {{ $milestone->status }}</p>
                        </div>
                        <a href="{{ route('devmanager.milestones.edit', $milestone) }}" class="text-sm text-[#0094af] hover:underline">{{ __('Edit') }}</a>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $milestones->links() }}</div>
        @endif
    </div>
</div>
@endsection
