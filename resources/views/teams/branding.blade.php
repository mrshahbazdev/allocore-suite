@extends('layouts.shell', ['title' => __('Team branding')])

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Team branding') }}</h1>
    <p class="text-sm text-slate-500">{{ __('Customize the logo, colors and domain for :team.', ['team' => $team->name]) }}</p>
</div>

<form method="POST" action="{{ route('teams.branding.update', $team) }}" class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
    @csrf
    @method('PATCH')

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Subdomain') }}</label>
        <input type="text" name="subdomain" value="{{ old('subdomain', $team->subdomain) }}" placeholder="yourteam" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Custom domain') }}</label>
        <input type="text" name="custom_domain" value="{{ old('custom_domain', $team->custom_domain) }}" placeholder="tools.yourcompany.com" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Logo URL') }}</label>
        <input type="url" name="logo" value="{{ old('logo', $team->logo) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Favicon URL') }}</label>
        <input type="url" name="favicon" value="{{ old('favicon', $team->favicon) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Primary color') }}</label>
            <input type="color" name="primary_color" value="{{ old('primary_color', $team->primary_color ?? '#4f46e5') }}" class="mt-1 h-10 w-full rounded-lg border-slate-300 shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Accent color') }}</label>
            <input type="color" name="accent_color" value="{{ old('accent_color', $team->accent_color ?? '#0ea5e9') }}" class="mt-1 h-10 w-full rounded-lg border-slate-300 shadow-sm">
        </div>
    </div>

    <div class="pt-2">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Save branding') }}</button>
    </div>
</form>
@endsection
