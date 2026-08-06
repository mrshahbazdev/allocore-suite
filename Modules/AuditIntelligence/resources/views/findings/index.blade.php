@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Findings') }}</h1>
        <a href="{{ route('auditintelligence.findings.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('New Finding') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($findings->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No findings yet.') }}</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($findings as $finding)
                    <li class="flex flex-col gap-1 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <a href="{{ route('auditintelligence.findings.show', $finding) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $finding->title }}</a>
                            <p class="text-xs text-slate-500">{{ __($finding->risk_level) }} &middot; {{ __($finding->priority) }} &middot; {{ __($finding->status) }}</p>
                        </div>
                        <div class="flex gap-2 text-sm">
                            <span class="text-slate-500">{{ $finding->recommendations_count }} {{ __('recommendations') }}</span>
                            <span class="text-slate-300">|</span>
                            <span class="text-slate-500">{{ $finding->upsells_count }} {{ __('upsells') }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">{{ $findings->links() }}</div>
        @endif
    </div>
</div>
@endsection
