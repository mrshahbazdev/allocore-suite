@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <a href="{{ route('customersuccess.inquiries.index') }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ __('Inquiries') }}</a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $inquiry->question }}</h1>
        </div>
        <form method="POST" action="{{ route('customersuccess.inquiries.destroy', $inquiry) }}" onsubmit="return confirm('{{ __('Delete this inquiry?') }}')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
        </form>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @if($inquiry->problem)
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Problem') }}</h2>
                    <p class="mt-1 text-slate-700">{{ $inquiry->problem }}</p>
                </div>
            @endif
            @if($inquiry->root_cause)
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Root Cause') }}</h2>
                    <p class="mt-1 text-slate-700">{{ $inquiry->root_cause }}</p>
                </div>
            @endif
            @if($inquiry->consequences)
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Consequences') }}</h2>
                    <p class="mt-1 text-slate-700">{{ $inquiry->consequences }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @if($inquiry->recommended_actions)
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Recommended Actions') }}</h2>
                    <p class="mt-1 whitespace-pre-line text-slate-700">{{ $inquiry->recommended_actions }}</p>
                </div>
            @endif
            @if($inquiry->priority)
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Priority') }}</h2>
                    <p class="mt-1 text-slate-700">{{ __($inquiry->priority) }}</p>
                </div>
            @endif
            @if($inquiry->estimated_cost)
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Estimated Cost') }}</h2>
                    <p class="mt-1 text-slate-700">{{ $inquiry->estimated_cost }}</p>
                </div>
            @endif
            @if($inquiry->expected_benefit)
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Expected Benefit') }}</h2>
                    <p class="mt-1 text-slate-700">{{ $inquiry->expected_benefit }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($inquiry->answer)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Answer') }}</h2>
            <p class="mt-2 whitespace-pre-line text-slate-700">{{ $inquiry->answer }}</p>
        </div>
    @endif

    @if(! empty($inquiry->sources))
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Sources') }}</h2>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700">
                @foreach($inquiry->sources as $source)
                    <li>
                        <a href="{{ $source['url'] ?? '#' }}" class="text-[#0094af] hover:underline">{{ $source['title'] ?? __('Source') }}</a>
                        <span class="text-slate-500">({{ $source['source'] ?? '-' }})</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
