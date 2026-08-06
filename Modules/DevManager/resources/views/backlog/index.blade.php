@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Backlog') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($stories->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No user stories in the backlog.') }}</p>
        @else
            <div class="grid gap-6 md:grid-cols-3">
                @foreach($statuses as $status)
                    <div>
                        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __($status) }}</h2>
                        <div class="space-y-3">
                            @foreach($stories->where('status', $status) as $story)
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                    <p class="text-sm text-slate-700">{{ __('As a :role, I want :action', ['role' => $story->role, 'action' => $story->action]) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $story->idea->title }} &middot; {{ $story->story_points ?: '—' }} pts</p>
                                    <a href="{{ route('devmanager.user-stories.edit', $story) }}" class="mt-2 inline-block text-xs text-[#0094af] hover:underline">{{ __('Edit') }}</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $stories->links() }}</div>
        @endif
    </div>
</div>
@endsection
