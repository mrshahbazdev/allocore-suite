@extends('layouts.shell', ['title' => __('Organizations')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Organizations') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Manage the organizations in your OrgMatrix.') }}</p>
        </div>
        <a href="{{ route('orgmatrix.organizations.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('New Organization') }}
        </a>
    </div>

    @if ($organizations->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 12h6m-6 5.25h6"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('No organizations yet') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Start by creating your first organization.') }}</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($organizations as $organization)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-lg font-semibold text-slate-900">{{ $organization->name }}</div>
                            <div class="text-sm text-slate-500">{{ $organization->industry ?? __('No industry') }}</div>
                        </div>
                        <div class="flex gap-1">
                            <a href="{{ route('orgmatrix.organizations.edit', $organization) }}" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600" title="{{ __('Edit') }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            </a>
                            <form method="POST" action="{{ route('orgmatrix.organizations.destroy', $organization) }}" onsubmit="return confirm('{{ __('Delete this organization?') }}')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="{{ __('Delete') }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397M4.772 5.79L5.42 3.356A2.25 2.25 0 017.47 2.25h9.06a2.25 2.25 0 012.05 2.106l.648 2.434m-14.456 0a48.11 48.11 0 013.478-.397"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3 text-sm">
                        <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-indigo-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.637-2.911M15 19.128V13.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 13.5v3.75m-3-1.837a6.375 6.375 0 0111.637-2.911"/></svg>
                            {{ $organization->roles_count }} {{ __('roles') }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1 text-emerald-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.637-2.911M15 19.128V13.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 13.5v3.75m-3-1.837a6.375 6.375 0 0111.637-2.911"/></svg>
                            {{ $organization->people_count }} {{ __('people') }}
                        </span>
                    </div>
                    <a href="{{ route('orgmatrix.organizations.show', $organization) }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        {{ __('Open Organization') }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
