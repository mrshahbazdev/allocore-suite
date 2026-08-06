@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Customer Success Assistant') }}</h1>
        <a href="{{ route('customersuccess.inquiries.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('Ask Question') }}</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Inquiries') }}</p>
            <p class="mt-2 text-3xl font-bold text-[#0094af]">{{ $stats['inquiries'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('High Priority') }}</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['high'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Critical') }}</p>
            <p class="mt-2 text-3xl font-bold text-rose-600">{{ $stats['critical'] }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Inquiries') }}</h2>
        @if($recent->isEmpty())
            <p class="mt-4 text-sm text-slate-500">{{ __('No inquiries yet.') }}</p>
        @else
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach($recent as $inquiry)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <a href="{{ route('customersuccess.inquiries.show', $inquiry) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $inquiry->question }}</a>
                            <p class="text-xs text-slate-500">{{ $inquiry->priority ? __($inquiry->priority) : '-' }} &middot; {{ $inquiry->created_at->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('customersuccess.inquiries.show', $inquiry) }}" class="text-sm text-[#0094af] hover:underline">{{ __('View') }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
