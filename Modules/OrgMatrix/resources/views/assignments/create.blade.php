@extends('layouts.shell', ['title' => __('Assign Person')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('Assign to') }} {{ $role->name }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('orgmatrix.organizations.roles.assignments.store', [$organization, $role]) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Person') }}</label>
                <select name="person_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    @foreach ($availablePeople as $person)
                        <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_primary" value="0">
                <input type="checkbox" name="is_primary" value="1" class="rounded border-slate-300 text-indigo-600">
                <label class="text-sm text-slate-700">{{ __('Primary representative') }}</label>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Succession horizon') }}</label>
                    <select name="succession_horizon" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                        <option value="">—</option>
                        @foreach (Modules\OrgMatrix\Models\RoleAssignment::HORIZONS as $h)
                            <option value="{{ $h }}">{{ ucfirst($h) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Readiness score') }}</label>
                    <input type="number" name="readiness_score" min="1" max="5" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Start date') }}</label>
                    <input type="date" name="start_date" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('End date') }}</label>
                    <input type="date" name="end_date" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                <textarea name="notes" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('orgmatrix.organizations.roles.index', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Assign') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
