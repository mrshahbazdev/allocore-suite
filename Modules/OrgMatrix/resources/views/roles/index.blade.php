@extends('layouts.shell', ['title' => __('Roles')])

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $organization->name }} — {{ __('Roles') }}</h1>
        <a href="{{ route('orgmatrix.organizations.roles.create', $organization) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Role') }}</a>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('orgmatrix.organizations.export.roles', $organization) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Export CSV') }}</a>
        <span class="text-slate-300">|</span>
        <form method="POST" action="{{ route('orgmatrix.organizations.import.roles', $organization) }}" enctype="multipart/form-data" class="flex gap-2 items-center">
            @csrf
            <input type="file" name="csv_file" accept=".csv,.txt" class="text-sm">
            <button class="text-sm text-indigo-600 hover:underline">{{ __('Import CSV') }}</button>
        </form>
    </div>

    @if ($roles->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No roles yet.') }}</div>
    @else
        <div class="space-y-3">
            @foreach ($roles as $role)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $role->name }}</div>
                            <div class="text-sm text-slate-500">{{ $role->department }} — {{ $role->criticality }} — {{ $role->assignments_count }} {{ __('assignments') }}</div>
                            @foreach ($role->assignments as $assignment)
                                <div class="text-sm text-slate-600 mt-1">
                                    {{ $assignment->person->full_name }}{{ $assignment->is_primary ? ' (Primary)' : '' }}
                                    {{ $assignment->succession_horizon ? ' — '.$assignment->succession_horizon : '' }}
                                </div>
                            @endforeach
                        </div>
                        <div class="flex gap-2 text-sm">
                            <a href="{{ route('orgmatrix.organizations.roles.assignments.create', [$organization, $role]) }}" class="text-indigo-600 hover:underline">{{ __('Assign') }}</a>
                            <a href="{{ route('orgmatrix.organizations.roles.edit', [$organization, $role]) }}" class="text-slate-600 hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('orgmatrix.organizations.roles.destroy', [$organization, $role]) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
