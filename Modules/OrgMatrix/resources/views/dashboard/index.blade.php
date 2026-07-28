@extends('layouts.shell', ['title' => __('OrgMatrix Dashboard')])

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('OrgMatrix') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Map roles, people and assignments across your organization.') }}</p>
        </div>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('orgmatrix.organizations.demo') }}">
                @csrf
                <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('Demo Organization') }}
                </button>
            </form>
            <a href="{{ route('orgmatrix.organizations.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
                {{ __('New Organization') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 12h6m-6 5.25h6"/></svg>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Organizations') }}</div>
                    <div class="text-2xl font-bold text-slate-900">{{ $organizations->count() }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.637-2.911M15 19.128V13.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 13.5v3.75m-3-1.837a6.375 6.375 0 0111.637-2.911"/></svg>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Total Roles') }}</div>
                    <div class="text-2xl font-bold text-slate-900">{{ $total_roles }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.637-2.911M15 19.128V13.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 13.5v3.75m-3-1.837a6.375 6.375 0 0111.637-2.911"/></svg>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Total People') }}</div>
                    <div class="text-2xl font-bold text-slate-900">{{ $total_people }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($organizations->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 12h6m-6 5.25h6"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('No organizations yet') }}</h3>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">{{ __('Create your first organization, or generate a demo organization with sample roles, people and assignments.') }}</p>
            <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <form method="POST" action="{{ route('orgmatrix.organizations.demo') }}">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        {{ __('Create Demo Organization') }}
                    </button>
                </form>
                <a href="{{ route('orgmatrix.organizations.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
                    {{ __('Create Organization') }}
                </a>
            </div>
        </div>
    @else
        <div>
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Organizations') }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($organizations as $organization)
                    <a href="{{ route('orgmatrix.organizations.show', $organization) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-semibold text-slate-900 group-hover:text-indigo-600">{{ $organization->name }}</div>
                                <div class="text-sm text-slate-500">{{ $organization->industry ?? __('No industry') }}</div>
                            </div>
                            <div class="rounded-full bg-slate-100 p-2 text-slate-500 group-hover:bg-indigo-50 group-hover:text-indigo-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-4 text-sm text-slate-600">
                            <span class="rounded-lg bg-slate-50 px-2 py-1">{{ $organization->roles_count }} {{ __('roles') }}</span>
                            <span class="rounded-lg bg-slate-50 px-2 py-1">{{ $organization->people_count }} {{ __('people') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
