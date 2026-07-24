@extends('layouts.shell', ['title' => __('Notification preferences')])

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Notification preferences') }}</h1>
    <p class="text-sm text-slate-500">{{ __('Choose how and where you want to be notified.') }}</p>
</div>

<form method="POST" action="{{ route('notifications.preferences.update') }}">
    @csrf
    @method('PATCH')

    <div class="space-y-6">
        @foreach ($preferences as $type => $preference)
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold capitalize text-slate-900">{{ $type }}</h2>

                <div class="grid gap-4 sm:grid-cols-3">
                    <label class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                        <input type="hidden" name="preferences[{{ $type }}][in_app]" value="0">
                        <input type="checkbox" name="preferences[{{ $type }}][in_app]" value="1" {{ $preference->in_app ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                        <span class="text-sm font-medium text-slate-700">{{ __('In-app') }}</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                        <input type="hidden" name="preferences[{{ $type }}][email]" value="0">
                        <input type="checkbox" name="preferences[{{ $type }}][email]" value="1" {{ $preference->email ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                        <span class="text-sm font-medium text-slate-700">{{ __('Email') }}</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                        <input type="hidden" name="preferences[{{ $type }}][push]" value="0">
                        <input type="checkbox" name="preferences[{{ $type }}][push]" value="1" {{ $preference->push ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                        <span class="text-sm font-medium text-slate-700">{{ __('Push') }}</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                        <input type="hidden" name="preferences[{{ $type }}][slack]" value="0">
                        <input type="checkbox" name="preferences[{{ $type }}][slack]" value="1" {{ $preference->slack ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                        <span class="text-sm font-medium text-slate-700">{{ __('Slack') }}</span>
                    </label>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700">{{ __('Slack webhook URL') }}</label>
                    <input type="url" name="preferences[{{ $type }}][slack_webhook]" value="{{ $preference->slack_webhook }}" placeholder="https://hooks.slack.com/services/..." class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Save preferences') }}</button>
    </div>
</form>
@endsection
