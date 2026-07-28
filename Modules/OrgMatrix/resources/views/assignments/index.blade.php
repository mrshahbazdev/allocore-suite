@extends('layouts.shell', ['title' => __('Role Assignments')])

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $organization->name }} — {{ __('Role Assignments') }}</h1>
        <a href="{{ route('orgmatrix.organizations.show', $organization) }}" class="text-indigo-600 hover:underline">{{ __('Back') }}</a>
    </div>

    @if ($assignments->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No assignments yet.') }}</div>
    @else
        <div class="space-y-3">
            @foreach ($assignments as $assignment)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $assignment->role->name }}</div>
                            <div class="text-sm text-slate-500">{{ $assignment->person->full_name }}{{ $assignment->is_primary ? ' — '.__('Primary') : '' }}</div>
                            <div class="text-sm text-slate-600 mt-1">
                                {{ $assignment->succession_horizon ? ucfirst($assignment->succession_horizon).' ' : '' }}{{ $assignment->readiness_score ? __('Readiness').': '.$assignment->readiness_score : '' }}
                            </div>
                        </div>
                        <form method="POST" action="{{ route('orgmatrix.organizations.roles.assignments.destroy', [$organization, $assignment->role, $assignment]) }}" onsubmit="return confirm('{{ __('Remove assignment?') }}')">
                            @csrf @method('DELETE')
                            <button class="text-sm text-rose-600 hover:underline">{{ __('Remove') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
