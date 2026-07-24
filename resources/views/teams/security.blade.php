@extends('layouts.shell', ['title' => __('Team security')])

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Team security') }}</h1>
    <p class="text-sm text-slate-500">{{ __('Require two-factor authentication for all members of :team.', ['team' => $team->name]) }}</p>
</div>

<form method="POST" action="{{ route('teams.security.update', $team) }}" class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
    @csrf
    @method('PATCH')

    <div class="flex items-start gap-3">
        <input type="checkbox" name="requires_two_factor" value="1" id="requires_two_factor" {{ old('requires_two_factor', $team->requires_two_factor) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        <div>
            <label for="requires_two_factor" class="block text-sm font-medium text-slate-900">{{ __('Require two-factor authentication') }}</label>
            <p class="text-xs text-slate-500">{{ __('Members without 2FA enabled will be redirected to the 2FA setup page until they enable it.') }}</p>
        </div>
    </div>

    <div class="pt-2">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Save security settings') }}</button>
    </div>
</form>
@endsection
