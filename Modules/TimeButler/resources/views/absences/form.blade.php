@extends('layouts.shell')

@section('title', __('New Absence'))
@section('page-title', __('New Absence Request'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Request Absence') }}</h1>

        <form method="POST" action="{{ route('timebutler.absences.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Absence Type') }}</label>
                <select name="absence_type_id" class="mt-1 w-full rounded-lg border-slate-300" required>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Start Date') }}</label>
                    <input type="date" name="start_date" class="mt-1 w-full rounded-lg border-slate-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('End Date') }}</label>
                    <input type="date" name="end_date" class="mt-1 w-full rounded-lg border-slate-300" required>
                </div>
            </div>

            <div class="flex gap-6">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="half_day_start" value="1" class="rounded border-slate-300">
                    {{ __('Half day start') }}
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="half_day_end" value="1" class="rounded border-slate-300">
                    {{ __('Half day end') }}
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Substitute (optional)') }}</label>
                <select name="substitute_id" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($users as $u)
                        @if ($u->id !== auth()->id())
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
            </div>

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Submit Request') }}</button>
        </form>
    </div>
@endsection
