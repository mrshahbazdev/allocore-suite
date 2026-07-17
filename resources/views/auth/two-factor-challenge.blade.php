@extends('layouts.guest')

@section('content')
    <div class="w-full max-w-md">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Two-Factor Authentication') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('Enter the 6-digit code from your authenticator app or a recovery code.') }}</p>

        <form method="POST" action="{{ route('two-factor.challenge.store') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="code" class="block text-sm font-medium text-slate-700">{{ __('Authentication code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" maxlength="6" class="mt-2 block w-full rounded-lg border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="000000" autofocus>
            </div>

            <div class="relative">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-slate-500">{{ __('or') }}</span></div>
            </div>

            <div>
                <label for="recovery_code" class="block text-sm font-medium text-slate-700">{{ __('Recovery code') }}</label>
                <input id="recovery_code" name="recovery_code" type="text" class="mt-2 block w-full rounded-lg border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="xxxx-xxxx">
            </div>

            <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">{{ __('Verify') }}</button>
        </form>
    </div>
@endsection
