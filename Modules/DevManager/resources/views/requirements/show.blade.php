@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('devmanager.requirements.index', $requirement->idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ __('Requirements') }}</a>
    <h1 class="text-2xl font-bold text-slate-900">{{ $requirement->title }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <p class="text-sm text-slate-500">{{ __('Priority') }}: {{ $requirement->priority }} &middot; {{ __('Status') }}: {{ $requirement->status }}</p>
        <p class="whitespace-pre-line text-sm text-slate-700">{{ $requirement->description ?: '—' }}</p>
        <div class="flex gap-2">
            <a href="{{ route('devmanager.requirements.edit', $requirement) }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('devmanager.requirements.destroy', $requirement) }}" onsubmit="return confirm('{{ __("Delete this requirement?") }}')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
