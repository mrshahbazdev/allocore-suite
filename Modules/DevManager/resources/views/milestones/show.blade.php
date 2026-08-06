@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('devmanager.milestones.index', $milestone->idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ __('Milestones') }}</a>
    <h1 class="text-2xl font-bold text-slate-900">{{ $milestone->title }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <p class="text-sm text-slate-500">{{ __('Due') }}: {{ $milestone->due_date?->format('M d, Y') ?: '—' }} &middot; {{ __('Status') }}: {{ $milestone->status }}</p>
        <p class="whitespace-pre-line text-sm text-slate-700">{{ $milestone->description ?: '—' }}</p>
        <div class="flex gap-2">
            <a href="{{ route('devmanager.milestones.edit', $milestone) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('devmanager.milestones.destroy', $milestone) }}" onsubmit="return confirm('{{ __("Delete this milestone?") }}')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
