@extends('layouts.shell', ['title' => __('OrgMatrix Dashboard')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('OrgMatrix') }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Organizations') }}</div>
            <div class="text-3xl font-bold text-indigo-600">{{ $organizations->count() }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Total Roles') }}</div>
            <div class="text-3xl font-bold text-indigo-600">{{ $total_roles }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Total People') }}</div>
            <div class="text-3xl font-bold text-indigo-600">{{ $total_people }}</div>
        </div>
    </div>

    <div class="flex justify-end">
        <a href="{{ route('orgmatrix.organizations.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Organization') }}</a>
    </div>

    @if ($organizations->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No organizations yet.') }}</div>
    @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($organizations as $organization)
                <a href="{{ route('orgmatrix.organizations.show', $organization) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                    <div class="font-semibold text-slate-900">{{ $organization->name }}</div>
                    <div class="text-sm text-slate-500 mt-1">{{ $organization->industry ?? '' }}</div>
                    <div class="mt-3 flex gap-4 text-sm text-slate-600">
                        <span>{{ $organization->roles_count }} {{ __('roles') }}</span>
                        <span>{{ $organization->people_count }} {{ __('people') }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
