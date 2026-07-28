@extends('layouts.shell', ['title' => __('Role Assignments')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ $organization->name }}</h1>
            <p class="text-sm text-slate-500">{{ __('All role assignments') }}</p>
        </div>
        <a href="{{ route('orgmatrix.organizations.roles.index', $organization) }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            {{ __('Back to Roles') }}
        </a>
    </div>

    @if ($assignments->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <h3 class="text-lg font-semibold text-slate-900">{{ __('No assignments yet') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Go to a role and click Assign to add a person.') }}</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Role') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Person') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Type') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Readiness') }}</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($assignments as $assignment)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 text-sm font-semibold text-slate-900">{{ $assignment->role->name }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $assignment->person->full_name }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700">
                                @if ($assignment->is_primary)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Primary') }}</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ __('Secondary') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-16 rounded-full bg-slate-200">
                                        <div class="h-2 rounded-full bg-indigo-600" style="width: {{ ($assignment->readiness_score ?? 0) * 20 }}%"></div>
                                    </div>
                                    <span>{{ $assignment->readiness_score ?? 0 }}/5</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right text-sm">
                                <form method="POST" action="{{ route('orgmatrix.organizations.roles.assignments.destroy', [$organization, $assignment->role, $assignment]) }}" onsubmit="return confirm('{{ __('Remove assignment?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                        {{ __('Remove') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
