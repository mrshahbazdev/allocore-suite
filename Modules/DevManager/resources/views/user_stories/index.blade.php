@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('devmanager.ideas.show', $idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ $idea->title }}</a>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('User Stories') }}</h1>
        </div>
        <a href="{{ route('devmanager.user-stories.create', $idea) }}" class="rounded-lg bg-[#0094af] px-4 py-2 text-sm font-semibold text-white hover:bg-[#007a8f]">{{ __('Add User Story') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($stories->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No user stories yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-slate-100 text-left text-xs uppercase text-slate-500"><tr><th class="pb-3 pr-4">{{ __('Story') }}</th><th class="pb-3 pr-4">{{ __('Points') }}</th><th class="pb-3 pr-4">{{ __('Status') }}</th><th class="pb-3 pr-4 text-right">{{ __('Actions') }}</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($stories as $story)
                            <tr>
                                <td class="py-3 pr-4 text-sm text-slate-700">{{ __('As a :role, I want :action so that :benefit', ['role' => $story->role, 'action' => $story->action, 'benefit' => $story->benefit ?: '…']) }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $story->story_points ?: '—' }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $story->status }}</td>
                                <td class="py-3 pr-4 text-right"><a href="{{ route('devmanager.user-stories.edit', $story) }}" class="text-sm text-[#0094af] hover:underline">{{ __('Edit') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $stories->links() }}</div>
        @endif
    </div>
</div>
@endsection
