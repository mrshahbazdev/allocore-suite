@extends('layouts.shell', ['title' => __('Nur-Du Dashboard')])

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Nur-Du') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Align vision, quarterly priorities, decisions and regular checks.') }}</p>
        </div>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('nurdu.demo') }}">
                @csrf
                <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('Demo Data') }}
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Principles') }}</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ $vision?->guidingPrinciples->count() ?? 0 }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Priorities') }}</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ $quarterlyFocus?->strategicPriorities->count() ?? 0 }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Decisions') }}</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ array_sum($decisionStats) }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Checks') }}</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ ($vision?->guidingPrinciples->count() ?? 0) > 0 || ($quarterlyFocus?->strategicPriorities->count() ?? 0) > 0 || array_sum($decisionStats) > 0 ? 1 : 0 }}</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Vision') }}</h2>
                <a href="{{ route('nurdu.vision.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ __('Manage') }}</a>
            </div>
            @if ($vision)
                <p class="mt-3 text-slate-700">{{ $vision->statement }}</p>
                @if ($vision->guidingPrinciples->isNotEmpty())
                    <ul class="mt-4 space-y-2">
                        @foreach ($vision->guidingPrinciples as $principle)
                            <li class="flex items-start gap-2 text-sm text-slate-600">
                                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $principle->title }}{{ $principle->description ? ': '.$principle->description : '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @else
                <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.214.13.428.183.641M9.954 9.784c-.463.598-.697 1.304-.697 2.016 0 .713.234 1.419.697 2.016m4.092-4c.463.597.697 1.303.697 2.016 0 .713-.234 1.419-.697 2.016M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">{{ __('No vision statement yet. Write one or use Demo Data.') }}</p>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Quarterly Focus') }} {{ $currentQuarter }} {{ $currentYear }}</h2>
                <a href="{{ route('nurdu.quarterly.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ __('Manage') }}</a>
            </div>
            @if ($quarterlyFocus)
                <p class="mt-3 text-slate-600">{{ $quarterlyFocus->notes ?: __('No notes.') }}</p>
                @if ($quarterlyFocus->strategicPriorities->isNotEmpty())
                    <ul class="mt-4 space-y-2">
                        @foreach ($quarterlyFocus->strategicPriorities as $priority)
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-slate-700">{{ $priority->title }}</span>
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ match($priority->status) { 'on_track' => 'bg-emerald-100 text-emerald-700', 'at_risk' => 'bg-amber-100 text-amber-700', 'off_track' => 'bg-rose-100 text-rose-700', default => 'bg-slate-100 text-slate-700' } }}">{{ str_replace('_', ' ', $priority->status) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @else
                <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 6h13.5A2.25 2.25 0 0121 8.25v7.5"/></svg>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">{{ __('No quarterly focus yet. Add one or use Demo Data.') }}</p>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Decisions') }}</h2>
                <a href="{{ route('nurdu.decisions.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ __('Manage') }}</a>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-lg bg-emerald-50 p-3"><div class="text-xl font-bold text-emerald-700">{{ $decisionStats['green'] }}</div><div class="text-xs text-emerald-700">{{ __('Green') }}</div></div>
                <div class="rounded-lg bg-amber-50 p-3"><div class="text-xl font-bold text-amber-700">{{ $decisionStats['yellow'] }}</div><div class="text-xs text-amber-700">{{ __('Yellow') }}</div></div>
                <div class="rounded-lg bg-rose-50 p-3"><div class="text-xl font-bold text-rose-700">{{ $decisionStats['red'] }}</div><div class="text-xs text-rose-700">{{ __('Red') }}</div></div>
            </div>
            <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-slate-100">
                @php $total = max(1, array_sum($decisionStats)); @endphp
                <div class="flex h-full">
                    <div class="h-full bg-emerald-500" style="width: {{ ($decisionStats['green'] / $total) * 100 }}%"></div>
                    <div class="h-full bg-amber-400" style="width: {{ ($decisionStats['yellow'] / $total) * 100 }}%"></div>
                    <div class="h-full bg-rose-500" style="width: {{ ($decisionStats['red'] / $total) * 100 }}%"></div>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('Recent Decisions') }}</h3>
                @if ($recentDecisions->isEmpty())
                    <p class="mt-1 text-sm text-slate-500">{{ __('No decisions yet.') }}</p>
                @else
                    <ul class="mt-2 space-y-2">
                        @foreach ($recentDecisions as $decision)
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-slate-700">{{ $decision->title }}</span>
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ match($decision->alignment) { 'green' => 'bg-emerald-100 text-emerald-700', 'yellow' => 'bg-amber-100 text-amber-700', 'red' => 'bg-rose-100 text-rose-700', default => 'bg-slate-100 text-slate-700' } }}">{{ $decision->alignment }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Latest Vision Check') }}</h2>
                <a href="{{ route('nurdu.checks.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ __('Manage') }}</a>
            </div>
            @if ($latestCheck)
                <div class="mt-3 flex items-center gap-2 text-sm text-slate-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 6h13.5A2.25 2.25 0 0121 8.25v7.5"/></svg>
                    {{ $latestCheck->check_date->format('Y-m-d') }}
                </div>
                <p class="mt-2 text-sm text-slate-700">{{ Str::limit($latestCheck->q2_answer, 120) }}</p>
                @if ($latestCheck->actionItems->isNotEmpty())
                    <ul class="mt-3 space-y-1">
                        @foreach ($latestCheck->actionItems as $item)
                            <li class="flex items-center gap-2 text-sm {{ $item->completed ? 'text-emerald-600 line-through' : 'text-slate-700' }}">
                                <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $item->title }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            @else
                <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">{{ __('No vision checks yet. Run one or use Demo Data.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
