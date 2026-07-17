<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
        <p class="mt-2 text-sm text-slate-500">Sign in to access your dashboard, modules, and admin tools.</p>
    </div>

    <x-auth-session-status class="mb-6 rounded-xl border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="form.email" id="email" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm" type="email" name="email" required autofocus autocomplete="username" placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="form.password" id="password" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember" class="inline-flex items-center gap-2">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-indigo-600 hover:text-indigo-500 hover:underline" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center rounded-xl bg-indigo-600 px-4 py-3 text-base font-semibold uppercase tracking-wide text-white shadow-lg hover:bg-indigo-500 focus:ring-indigo-500">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 text-center text-sm text-slate-500">
            {{ __("Don't have an account?") }}
            <a href="{{ route('register') }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline">{{ __('Create one') }}</a>
        </p>
    @endif
</div>
