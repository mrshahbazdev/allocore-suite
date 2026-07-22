@extends('layouts.shell', ['title' => $organization->name])

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $organization->name }}</h1>
            <p class="text-sm text-slate-500">{{ $organization->description }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('visionflow.organizations.edit', $organization) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Values') }}</div><div class="text-2xl font-bold">{{ $organization->values_count }}</div></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Principles') }}</div><div class="text-2xl font-bold">{{ $organization->principles_count }}</div></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Goals') }}</div><div class="text-2xl font-bold">{{ $organization->strategic_goals_count }}</div></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Missions') }}</div><div class="text-2xl font-bold">{{ $organization->missions_count }}</div></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Projects') }}</div><div class="text-2xl font-bold">{{ $organization->projects_count }}</div></div>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('visionflow.organizations.values.index', $organization) }}" class="rounded-lg bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Values') }}</a>
        <a href="{{ route('visionflow.organizations.principles.index', $organization) }}" class="rounded-lg bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Principles') }}</a>
        <a href="{{ route('visionflow.organizations.strategic-goals.index', $organization) }}" class="rounded-lg bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Strategic Goals') }}</a>
        <a href="{{ route('visionflow.organizations.visions.index', $organization) }}" class="rounded-lg bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Visions') }}</a>
        <a href="{{ route('visionflow.organizations.missions.index', $organization) }}" class="rounded-lg bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Missions') }}</a>
        <a href="{{ route('visionflow.organizations.projects.index', $organization) }}" class="rounded-lg bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Projects') }}</a>
        <a href="{{ route('visionflow.organizations.decision-logs.index', $organization) }}" class="rounded-lg bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Decision Log') }}</a>
    </div>
</div>
@endsection
