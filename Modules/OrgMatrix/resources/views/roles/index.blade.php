@extends('layouts.shell', ['title' => __('Roles')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ $organization->name }}</h1>
            <p class="text-sm text-slate-500">{{ __('Roles & assignments') }}</p>
        </div>
        <a href="{{ route('orgmatrix.organizations.roles.create', $organization) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('New Role') }}
        </a>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <a href="{{ route('orgmatrix.organizations.export.roles', $organization) }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            {{ __('Export CSV') }}
        </a>
        <form method="POST" action="{{ route('orgmatrix.organizations.import.roles', $organization) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
            @csrf
            <input type="file" name="csv_file" accept=".csv,.txt" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 file:mr-2 file:border-0 file:bg-transparent file:text-sm file:font-medium">
            <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                {{ __('Import CSV') }}
            </button>
        </form>
    </div>

    @if ($roles->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <h3 class="text-lg font-semibold text-slate-900">{{ __('No roles yet') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Create a role to start assigning people.') }}</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($roles as $role)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $role->name }}</h3>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                    @if ($role->criticality === 'critical') bg-rose-100 text-rose-700
                                    @elseif ($role->criticality === 'high') bg-amber-100 text-amber-700
                                    @elseif ($role->criticality === 'medium') bg-sky-100 text-sky-700
                                    @else bg-slate-100 text-slate-600 @endif">{{ ucfirst($role->criticality) }}</span>
                            </div>
                            <div class="text-sm text-slate-500">{{ $role->department }} &middot; {{ $role->assignments_count }} {{ __('assignments') }}</div>

                            @if ($role->assignments->isNotEmpty())
                                <ul class="mt-3 space-y-2">
                                    @foreach ($role->assignments as $assignment)
                                        <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm">
                                            <span class="text-slate-700">
                                                {{ $assignment->person->full_name }}
                                                @if ($assignment->is_primary) <span class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">{{ __('Primary') }}</span> @endif
                                                @if ($assignment->succession_horizon) <span class="ml-2 text-slate-500">&middot; {{ $assignment->succession_horizon }}</span> @endif
                                            </span>
                                            <form method="POST" action="{{ route('orgmatrix.organizations.roles.assignments.destroy', [$organization, $role, $assignment]) }}" onsubmit="return confirm('{{ __('Remove assignment?') }}')">
                                                @csrf @method('DELETE')
                                                <button class="rounded-lg p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="{{ __('Remove') }}">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('orgmatrix.organizations.roles.assignments.create', [$organization, $role]) }}" class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                {{ __('Assign') }}
                            </a>
                            <a href="{{ route('orgmatrix.organizations.roles.edit', [$organization, $role]) }}" class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                                {{ __('Edit') }}
                            </a>
                            <form method="POST" action="{{ route('orgmatrix.organizations.roles.destroy', [$organization, $role]) }}" onsubmit="return confirm('{{ __('Delete this role?') }}')">
                                @csrf @method('DELETE')
                                <button class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-3 py-1.5 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
