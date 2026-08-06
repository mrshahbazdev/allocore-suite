@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Audit Intelligence Assistant') }}</h1>
        <a href="{{ route('auditintelligence.findings.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('New Finding') }}</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Findings') }}</p>
            <p class="mt-2 text-3xl font-bold text-[#0094af]">{{ $stats['findings'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Recommendations') }}</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['recommendations'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Upsells') }}</p>
            <p class="mt-2 text-3xl font-bold text-[#ff9200]">{{ $stats['upsells'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Critical') }}</p>
            <p class="mt-2 text-3xl font-bold text-rose-600">{{ $stats['critical'] }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Findings') }}</h2>
        @if($findings->isEmpty())
            <p class="mt-4 text-sm text-slate-500">{{ __('No findings yet.') }}</p>
        @else
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach($findings as $finding)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <a href="{{ route('auditintelligence.findings.show', $finding) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $finding->title }}</a>
                            <p class="text-xs text-slate-500">{{ __($finding->risk_level) }} &middot; {{ __($finding->status) }}</p>
                        </div>
                        <a href="{{ route('auditintelligence.findings.show', $finding) }}" class="text-sm text-[#0094af] hover:underline">{{ __('View') }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
