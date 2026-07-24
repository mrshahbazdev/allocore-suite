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
        @if ($team->custom_domain)
            <div class="mt-2 flex items-center gap-2 text-sm">
                @if ($team->custom_domain_verified_at)
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('DNS verified') }}</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">{{ __('DNS not verified') }}</span>
                @endif
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ __('SSL: :status', ['status' => $team->ssl_status]) }}</span>
                @if ($team->ssl_expires_at)
                    <span class="text-slate-500">{{ __('Expires') }}: {{ $team->ssl_expires_at->format('Y-m-d') }}</span>
                @endif
            </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Save branding') }}</button>
        @if ($team->custom_domain)
            <form method="POST" action="{{ route('teams.branding.verify-domain', $team) }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Verify DNS') }}</button>
            </form>
            <form method="POST" action="{{ route('teams.branding.request-ssl', $team) }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Request SSL') }}</button>
            </form>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Logo URL') }}</label>
            <input type="url" name="logo" value="{{ old('logo', $team->logo) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Favicon URL') }}</label>
            <input type="url" name="favicon" value="{{ old('favicon', $team->favicon) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
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
</form>
@endsection
