@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('auditintelligence.findings.show', $finding) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ __('Finding') }}</a>
    <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $recommendation->issue }}</h1>

    <div class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($recommendation->solution)
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ __('Solution') }}</h2>
                <p class="mt-1 text-slate-700">{{ $recommendation->solution }}</p>
            </div>
        @endif
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ __('Responsible') }}</h2>
                <p class="text-slate-700">{{ $recommendation->responsible ?: '-' }}</p>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ __('Effort') }}</h2>
                <p class="text-slate-700">{{ __($recommendation->effort) }}</p>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ __('Status') }}</h2>
                <p class="text-slate-700">{{ __($recommendation->status) }}</p>
            </div>
        </div>
        @if($recommendation->related_sop)
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ __('Related SOP') }}</h2>
                <p class="text-slate-700">{{ $recommendation->related_sop }}</p>
            </div>
        @endif
    </div>

    <div class="mt-4 flex gap-2">
        <a href="{{ route('auditintelligence.recommendations.edit', [$finding, $recommendation]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
        <form method="POST" action="{{ route('auditintelligence.recommendations.destroy', [$finding, $recommendation]) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
        </form>
    </div>
</div>
@endsection
