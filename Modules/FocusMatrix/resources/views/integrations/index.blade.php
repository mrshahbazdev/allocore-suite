@extends('layouts.shell', ['title' => __('Integrations')])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Integrations') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Google Calendar') }}</h2>
        @if ($google['connected'])
            <p class="text-sm text-emerald-700">{{ __('Connected') }}: {{ $google['account_email'] }}</p>
            <form method="POST" action="{{ route('focusmatrix.integrations.google.disconnect') }}">
                @csrf @method('DELETE')
                <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Disconnect') }}</button>
            </form>
        @else
            @if ($google_configured)
                <a href="{{ route('focusmatrix.integrations.google.connect') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Connect Google Calendar') }}</a>
            @else
                <p class="text-sm text-amber-700">{{ __('Google OAuth is not configured by the admin.') }}</p>
            @endif
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Webhooks') }}</h2>
        <form method="POST" action="{{ route('focusmatrix.integrations.webhook.connect', 'slack') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Slack webhook URL') }}</label>
                <input type="url" name="webhook_url" value="{{ $slack['connected'] ? ($slack['webhook_preview'] ?? '') : '' }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Label') }}</label>
                <input type="text" name="label" value="{{ $slack['label'] ?? '' }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save Slack') }}</button>
        </form>

        <form method="POST" action="{{ route('focusmatrix.integrations.webhook.connect', 'teams') }}" class="space-y-3 pt-4 border-t border-slate-100">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Teams webhook URL') }}</label>
                <input type="url" name="webhook_url" value="{{ $teams['connected'] ? ($teams['webhook_preview'] ?? '') : '' }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Label') }}</label>
                <input type="text" name="label" value="{{ $teams['label'] ?? '' }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save Teams') }}</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Calendar Feed (ICS)') }}</h2>
        <p class="text-sm text-slate-500">{{ __('Use this URL in Outlook, Google Calendar or Apple Calendar:') }}</p>
        <input type="text" readonly value="{{ $ics['url'] }}" class="block w-full rounded-lg border-slate-300 bg-slate-50 text-sm" onclick="this.select()">
        <form method="POST" action="{{ route('focusmatrix.integrations.ics.regenerate') }}">
            @csrf
            <button class="rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-500">{{ __('Regenerate token') }}</button>
        </form>
    </div>
</div>
@endsection
