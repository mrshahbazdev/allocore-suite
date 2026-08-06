@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('devmanager.releases.index', $release->idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ __('Releases') }}</a>
    <h1 class="text-2xl font-bold text-slate-900">v{{ $release->version }} {{ $release->title }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <p class="text-sm text-slate-500">{{ __('Released at') }}: {{ $release->released_at?->format('M d, Y') ?: '—' }} &middot; {{ __('Status') }}: {{ $release->status }}</p>
        <p class="whitespace-pre-line text-sm text-slate-700">{{ $release->summary ?: '—' }}</p>
        <h3 class="text-sm font-semibold text-slate-900">{{ __('Changelog') }}</h3>
        <p class="whitespace-pre-line text-sm text-slate-700">{{ $release->changelog ?: '—' }}</p>
        <div class="flex gap-2">
            <a href="{{ route('devmanager.releases.edit', $release) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('devmanager.releases.destroy', $release) }}" onsubmit="return confirm('{{ __("Delete this release?") }}')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
