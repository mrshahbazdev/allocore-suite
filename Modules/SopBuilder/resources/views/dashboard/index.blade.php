@php $title = __('SOP Builder'); @endphp
@extends('layouts.shell')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('SOP Builder') }}</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Dashboard') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Standard operating procedures, checklists & training.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sopbuilder.sops.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('All SOPs') }}</a>
            <a href="{{ route('sopbuilder.sops.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">{{ __('New SOP') }}</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase text-slate-500">{{ __('SOPs') }}</div>
            <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['sops'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase text-slate-500">{{ __('Published') }}</div>
            <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['published'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase text-slate-500">{{ __('Categories') }}</div>
            <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['categories'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase text-slate-500">{{ __('Completions') }}</div>
            <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['completions'] }}</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent SOPs') }}</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @forelse ($recentSops as $sop)
                    <li class="flex items-center justify-between rounded-lg border border-slate-100 p-3 hover:bg-slate-50">
                        <div>
                            <a href="{{ route('sopbuilder.sops.show', $sop) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $sop->title }}</a>
                            <div class="text-xs text-slate-500">{{ $sop->category?->name ?? __('Uncategorized') }} — v{{ $sop->version }}</div>
                        </div>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize {{ $sop->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $sop->status }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No SOPs yet.') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Completions') }}</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @forelse ($recentCompletions as $completion)
                    <li class="flex items-center justify-between rounded-lg border border-slate-100 p-3">
                        <div>
                            <div class="font-medium text-slate-900">{{ $completion->sop->title }}</div>
                            <div class="text-xs text-slate-500">{{ $completion->user?->name }} — {{ $completion->completed_at?->format('M d, Y H:i') }}</div>
                        </div>
                        @if($completion->score !== null)
                            <span class="text-sm font-semibold {{ $completion->score >= 80 ? 'text-emerald-600' : ($completion->score >= 50 ? 'text-amber-600' : 'text-rose-600') }}">{{ $completion->score }}%</span>
                        @endif
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No completions yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
