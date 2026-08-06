@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('devmanager.user-stories.index', $userStory->idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ __('User Stories') }}</a>
    <h1 class="text-2xl font-bold text-slate-900">{{ __('As a :role, I want :action', ['role' => $userStory->role, 'action' => $userStory->action]) }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <p class="text-sm text-slate-700">{{ __('So that :benefit', ['benefit' => $userStory->benefit ?: '…']) }}</p>
        <p class="text-sm text-slate-500">{{ __('Points') }}: {{ $userStory->story_points ?: '—' }} &middot; {{ __('Status') }}: {{ $userStory->status }}</p>
        <p class="whitespace-pre-line text-sm text-slate-700">{{ $userStory->acceptance_criteria ?: '—' }}</p>
        <div class="flex gap-2">
            <a href="{{ route('devmanager.user-stories.edit', $userStory) }}" class="rounded-lg bg-[#0094af] px-4 py-2 text-sm font-semibold text-white hover:bg-[#007a8f]">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('devmanager.user-stories.destroy', $userStory) }}" onsubmit="return confirm('{{ __("Delete this user story?") }}')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
