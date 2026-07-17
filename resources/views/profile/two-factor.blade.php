@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Two-Factor Authentication') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('Add an extra layer of security to your account.') }}</p>

        @if (session('status'))
            <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif

        @if ($enabled)
            <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold text-slate-900">{{ __('2FA is enabled') }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ __('You can disable two-factor authentication or regenerate your recovery codes below.') }}</p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('two-factor.regenerate') }}" class="inline">
                        @csrf
                        <label class="sr-only" for="regenerate-password">{{ __('Current password') }}</label>
                        <input id="regenerate-password" name="password" type="password" required class="rounded-lg border-slate-300 px-3 py-2 text-sm" placeholder="{{ __('Current password') }}">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Regenerate recovery codes') }}</button>
                    </form>

                    <form method="POST" action="{{ route('two-factor.destroy') }}" class="inline" onsubmit="return confirm('{{ __('Disable two-factor authentication?') }}')">
                        @csrf
                        @method('DELETE')
                        <input name="password" type="password" required class="rounded-lg border-slate-300 px-3 py-2 text-sm" placeholder="{{ __('Current password') }}">
                        <button type="submit" class="rounded-lg border border-rose-600 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Disable 2FA') }}</button>
                    </form>
                </div>
            </div>
        @else
            <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold text-slate-900">{{ __('Set up authenticator app') }}</h2>

                <div class="mt-4 grid gap-6 md:grid-cols-2">
                    <div class="flex items-center justify-center rounded-lg bg-white p-4">
                        {!! $qrCode !!}
                    </div>
                    <div>
                        <p class="text-sm text-slate-600">{{ __('Scan this QR code with your authenticator app, then enter the 6-digit code to confirm.') }}</p>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-slate-700">{{ __('Secret key') }}</label>
                            <code class="mt-1 block break-all rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-900">{{ $secret }}</code>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('two-factor.store') }}" class="mt-6">
                    @csrf
                    <div>
                        <label for="code" class="block text-sm font-medium text-slate-700">{{ __('6-digit code') }}</label>
                        <input id="code" name="code" type="text" inputmode="numeric" maxlength="6" required class="mt-2 block w-full max-w-xs rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="000000">
                    </div>
                    <button type="submit" class="mt-4 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Enable 2FA') }}</button>
                </form>
            </div>
        @endif
    </div>
@endsection
