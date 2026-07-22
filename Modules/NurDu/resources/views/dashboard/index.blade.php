@extends('layouts.shell', ['title' => __('Nur-Du Dashboard')])

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
    @endif

    <h1 class="text-2xl font-bold text-slate-900">{{ __('Nur-Du Dashboard') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Vision') }}</h2>
        @if ($vision)
            <p class="mt-2 text-slate-700">{{ $vision->statement }}</p>
            @if ($vision->guidingPrinciples->isNotEmpty())
                <ul class="mt-4 list-disc list-inside text-sm text-slate-600">
                    @foreach ($vision->guidingPrinciples as $principle)
                        <li>{{ $principle->title }}</li>
                    @endforeach
                </ul>
            @endif
        @else
            <p class="text-slate-500">{{ __('No vision statement set.') }}</p>
        @endif
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Current Quarter') }} {{ $currentQuarter }} {{ $currentYear }}</h2>
            @if ($quarterlyFocus)
                <p class="mt-2 text-slate-600">{{ $quarterlyFocus->notes }}</p>
                <ul class="mt-4 space-y-2">
                    @foreach ($quarterlyFocus->strategicPriorities as $priority)
                        <li class="text-sm text-slate-700">{{ $priority->title }} <span class="rounded-full px-2 py-0.5 text-xs bg-slate-100">{{ $priority->status }}</span></li>
                    @endforeach
                </ul>
            @else
                <p class="text-slate-500">{{ __('No quarterly focus for this period.') }}</p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Decision Alignment') }}</h2>
            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-lg bg-emerald-50 p-3"><div class="text-xl font-bold text-emerald-700">{{ $decisionStats['green'] }}</div><div class="text-xs text-emerald-700">{{ __('Green') }}</div></div>
                <div class="rounded-lg bg-amber-50 p-3"><div class="text-xl font-bold text-amber-700">{{ $decisionStats['yellow'] }}</div><div class="text-xs text-amber-700">{{ __('Yellow') }}</div></div>
                <div class="rounded-lg bg-rose-50 p-3"><div class="text-xl font-bold text-rose-700">{{ $decisionStats['red'] }}</div><div class="text-xs text-rose-700">{{ __('Red') }}</div></div>
            </div>
            <div class="mt-4 h-3 w-full rounded-full bg-slate-100 overflow-hidden flex">
                @php $total = max(1, array_sum($decisionStats)); @endphp
                <div class="h-full bg-emerald-500" style="width: {{ ($decisionStats['green'] / $total) * 100 }}%"></div>
                <div class="h-full bg-amber-400" style="width: {{ ($decisionStats['yellow'] / $total) * 100 }}%"></div>
                <div class="h-full bg-rose-500" style="width: {{ ($decisionStats['red'] / $total) * 100 }}%"></div>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Decisions') }}</h2>
            @if ($recentDecisions->isEmpty())
                <p class="text-slate-500">{{ __('No decisions yet.') }}</p>
            @else
                <ul class="mt-4 space-y-2">
                    @foreach ($recentDecisions as $decision)
                        <li class="text-sm text-slate-700 flex justify-between"><span>{{ $decision->title }}</span> <span class="rounded-full px-2 py-0.5 text-xs bg-slate-100">{{ $decision->alignment }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Latest Vision Check') }}</h2>
            @if ($latestCheck)
                <p class="text-sm text-slate-500">{{ $latestCheck->check_date->format('Y-m-d') }}</p>
                <p class="mt-2 text-sm text-slate-700">{{ Str::limit($latestCheck->q2_answer, 100) }}</p>
                @if ($latestCheck->actionItems->isNotEmpty())
                    <ul class="mt-3 space-y-1">
                        @foreach ($latestCheck->actionItems as $item)
                            <li class="text-sm {{ $item->completed ? 'text-emerald-600 line-through' : 'text-slate-700' }}">{{ $item->title }}</li>
                        @endforeach
                    </ul>
                @endif
            @else
                <p class="text-slate-500">{{ __('No vision checks yet.') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
