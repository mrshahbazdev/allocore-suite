@extends('layouts.shell')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('ICP profile') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Define the ideal customer profile used for lead scoring.') }}</p>
        </div>

        <form method="POST" action="{{ route('leadquality.icp.store') }}" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                @foreach (['industry','employee_count_range','budget_min','budget_max','role','location'] as $field)
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">{{ ucfirst(str_replace('_', ' ', $field)) }}</span>
                        <input name="{{ $field }}" value="{{ old($field, $profile->{$field}) }}" class="w-full rounded-lg border-slate-300" />
                    </label>
                @endforeach
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">{{ __('Save profile') }}</button>
        </form>
    </div>
@endsection
