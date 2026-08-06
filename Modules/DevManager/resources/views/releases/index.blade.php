@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('devmanager.ideas.show', $idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ $idea->title }}</a>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Releases') }}</h1>
        </div>
        <a href="{{ route('devmanager.releases.create', $idea) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Add Release') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($releases->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No releases yet.') }}</p>
        @else
            <div class="space-y-4">
                @foreach($releases as $release)
                    <div class="flex items-start justify-between rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div>
                            <span class="text-xs font-semibold text-indigo-600">v{{ $release->version }}</span>
                            <a href="{{ route('devmanager.releases.edit', $release) }}" class="ml-2 font-medium text-slate-900 hover:text-[#ff9200]">{{ $release->title }}</a>
                            <p class="text-xs text-slate-500">{{ $release->released_at?->format('M d, Y') ?: __('Not released') }} &middot; {{ $release->status }}</p>
                        </div>
                        <a href="{{ route('devmanager.releases.edit', $release) }}" class="text-sm text-[#0094af] hover:underline">{{ __('Edit') }}</a>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $releases->links() }}</div>
        @endif
    </div>
</div>
@endsection
