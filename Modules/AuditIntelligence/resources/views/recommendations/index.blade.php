@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('auditintelligence.findings.show', $finding) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ __('Finding') }}</a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ __('Recommendations for') }} {{ $finding->title }}</h1>
        </div>
        <a href="{{ route('auditintelligence.recommendations.create', $finding) }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('Add Recommendation') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($recommendations->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No recommendations yet.') }}</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($recommendations as $recommendation)
                    <li class="py-4">
                        <p class="font-medium text-slate-900">{{ $recommendation->issue }}</p>
                        @if($recommendation->solution)
                            <p class="mt-1 text-sm text-slate-700">{{ $recommendation->solution }}</p>
                        @endif
                        <p class="mt-1 text-xs text-slate-500">{{ $recommendation->responsible }} &middot; {{ __($recommendation->effort) }} &middot; {{ __($recommendation->status) }}</p>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">{{ $recommendations->links() }}</div>
        @endif
    </div>
</div>
@endsection
