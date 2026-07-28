@extends('layouts.shell', ['title' => $item->title ?? $item->name ?? $item->statement ?? $item->content])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('visionflow.organizations.show', $organization) }}" class="hover:text-indigo-600">{{ $organization->name }}</a>
        <span>/</span>
        <a href="{{ route('visionflow.organizations.missions.index', $organization) }}" class="hover:text-indigo-600">{{ __('Missions') }}</a>
        <span>/</span>
        <span class="text-slate-900">{{ $item->title ?? $item->name ?? $item->statement ?? $item->content }}</span>
    </div>

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <h1 class="text-3xl font-bold text-slate-900">{{ $item->title ?? $item->name ?? $item->statement ?? $item->content }}</h1>
        <a href="{{ route('visionflow.organizations.missions.index', $organization) }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            {{ __('Back') }}
        </a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
        <div><div class="text-sm text-slate-500">{{ __("Title") }}</div><div class="mt-1 text-slate-900">{{ $item->title }}</div></div>
        <div><div class="text-sm text-slate-500">{{ __("Vision") }}</div><div class="mt-1 text-slate-900">{{ $item->vision->content ?? '-' }}</div></div>
        <div><div class="text-sm text-slate-500">{{ __("Description") }}</div><div class="mt-1 text-slate-900">{{ $item->description }}</div></div>
        <div><div class="text-sm text-slate-500">{{ __("Status") }}</div><div class="mt-1"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ match(strtolower($item->status)) { 'approved' => 'bg-emerald-100 text-emerald-700', 'active' => 'bg-emerald-100 text-emerald-700', 'completed' => 'bg-emerald-100 text-emerald-700', 'current' => 'bg-emerald-100 text-emerald-700', 'archived' => 'bg-slate-100 text-slate-600', 'on_hold' => 'bg-slate-100 text-slate-600', 'paused' => 'bg-slate-100 text-slate-600', 'proposed' => 'bg-amber-100 text-amber-700', 'reviewing' => 'bg-amber-100 text-amber-700', 'drafting' => 'bg-amber-100 text-amber-700', 'draft' => 'bg-slate-100 text-slate-600', default => 'bg-slate-100 text-slate-700' } }}">{{ $item->status }}</span></div></div>
        <div><div class="text-sm text-slate-500">{{ __("Owner") }}</div><div class="mt-1 text-slate-900">{{ $item->owner->name ?? '-' }}</div></div>
        <div><div class="text-sm text-slate-500">{{ __("Next Review At") }}</div><div class="mt-1 text-slate-900">{{ $item->next_review_at }}</div></div>
    </div>
</div>
@endsection
