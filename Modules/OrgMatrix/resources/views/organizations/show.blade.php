@extends('layouts.shell', ['title' => $organization->name])

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $organization->name }}</h1>
            <p class="text-sm text-slate-500">{{ $organization->industry }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('orgmatrix.organizations.chart', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Org Chart') }}</a>
            <a href="{{ route('orgmatrix.organizations.edit', $organization) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Total Roles') }}</div><div class="text-2xl font-bold">{{ $stats['total_roles'] }}</div></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Total People') }}</div><div class="text-2xl font-bold">{{ $stats['total_people'] }}</div></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Active Roles') }}</div><div class="text-2xl font-bold">{{ $stats['active_roles'] }}</div></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Unassigned') }}</div><div class="text-2xl font-bold">{{ $stats['unassigned_roles'] }}</div></div>
    </div>

    <p class="text-slate-700">{{ $organization->description }}</p>

    <div class="flex gap-3">
        <a href="{{ route('orgmatrix.organizations.roles.index', $organization) }}" class="rounded-lg bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Manage Roles') }}</a>
        <a href="{{ route('orgmatrix.organizations.people.index', $organization) }}" class="rounded-lg bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Manage People') }}</a>
    </div>
</div>
@endsection
