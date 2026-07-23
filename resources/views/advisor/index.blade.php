@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('AI Advisor') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Personalized next steps based on your tools and data.') }}</p>
    </div>

    @if (empty($recommendations))
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('Great job! No immediate recommendations.') }}</div>
    @else
        <div class="grid gap-4">
            @foreach ($recommendations as $tip)
                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ ucfirst(str_replace(['-','_'], ' ', $tip['moduleKey'])) }}</span>
                            @if ($tip['priority'] >= 2)
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">{{ __('High impact') }}</span>
                            @endif
                        </div>
                        <h3 class="mt-2 font-semibold text-slate-900">{{ $tip['title'] }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $tip['description'] }}</p>
                    </div>
                    <a href="{{ $tip['actionUrl'] }}" class="inline-flex shrink-0 items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Take action') }}</a>
                </div>
            @endforeach
        </div>
    @endif
@endsection
