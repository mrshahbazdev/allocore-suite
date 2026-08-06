@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('auditintelligence.findings.index') }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ __('Findings') }}</a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $finding->title }}</h1>
            <p class="text-sm text-slate-500">{{ __($finding->risk_level) }} &middot; {{ __($finding->priority) }} &middot; {{ __($finding->status) }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('auditintelligence.findings.edit', $finding) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('auditintelligence.findings.destroy', $finding) }}" onsubmit="return confirm('{{ __('Delete this finding?') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>

    @if($finding->description)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Description') }}</h2>
            <p class="mt-2 whitespace-pre-line text-slate-700">{{ $finding->description }}</p>
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recommendations') }}</h2>
            @if($finding->recommendations->isEmpty())
                <p class="mt-2 text-sm text-slate-500">{{ __('No recommendations yet.') }}</p>
            @else
                <ul class="mt-2 space-y-3">
                    @foreach($finding->recommendations as $recommendation)
                        <li class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <div class="flex items-start justify-between">
                                <p class="font-medium text-slate-900">{{ $recommendation->issue }}</p>
                                <a href="{{ route('auditintelligence.recommendations.edit', [$finding, $recommendation]) }}" class="text-sm text-[#0094af] hover:underline">{{ __('Edit') }}</a>
                            </div>
                            @if($recommendation->solution)
                                <p class="mt-1 text-sm text-slate-700">{{ $recommendation->solution }}</p>
                            @endif
                            <p class="mt-2 text-xs text-slate-500">{{ $recommendation->responsible }} &middot; {{ __($recommendation->effort) }} &middot; {{ __($recommendation->status) }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
            <a href="{{ route('auditintelligence.recommendations.create', $finding) }}" class="mt-4 inline-block text-sm text-[#0094af] hover:underline">{{ __('Add Recommendation') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Upsell Opportunities') }}</h2>
            @if($finding->upsells->isEmpty())
                <p class="mt-2 text-sm text-slate-500">{{ __('No upsells yet.') }}</p>
            @else
                <ul class="mt-2 space-y-3">
                    @foreach($finding->upsells as $upsell)
                        <li class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <div class="flex items-start justify-between">
                                <p class="font-medium text-slate-900">{{ $upsell->name }}</p>
                                <a href="{{ route('auditintelligence.upsells.edit', [$finding, $upsell]) }}" class="text-sm text-[#0094af] hover:underline">{{ __('Edit') }}</a>
                            </div>
                            <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ __($upsell->type) }}</p>
                            @if($upsell->description)
                                <p class="mt-1 text-sm text-slate-700">{{ $upsell->description }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
            <a href="{{ route('auditintelligence.upsells.create', $finding) }}" class="mt-4 inline-block text-sm text-[#0094af] hover:underline">{{ __('Add Upsell') }}</a>
        </div>
    </div>
</div>
@endsection
