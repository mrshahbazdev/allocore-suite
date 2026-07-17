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
        <h1 class="text-2xl font-bold text-slate-900">{{ __('auth.welcome_back') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('auth.login_subtitle') }}</p>
    </div>

    <x-auth-session-status class="mb-6 rounded-lg border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('auth.email')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="form.email" id="email" class="mt-2 block w-full rounded-lg border-slate-300 px-4 py-3 shadow-sm" type="email" name="email" required autofocus autocomplete="username" placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('auth.password')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="form.password" id="password" class="mt-2 block w-full rounded-lg border-slate-300 px-4 py-3 shadow-sm"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember" class="inline-flex items-center gap-2">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="text-sm text-slate-600">{{ __('auth.remember_me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-indigo-600 hover:text-indigo-500 hover:underline" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('auth.forgot_password') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center rounded-lg bg-indigo-600 px-4 py-3 text-base font-semibold normal-case tracking-normal text-white shadow-sm hover:bg-indigo-700 focus:ring-indigo-500">
                {{ __('auth.log_in') }}
            </x-primary-button>
        </div>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 text-center text-sm text-slate-500">
            {{ __('auth.no_account') }}
            <a href="{{ route('register') }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline">{{ __('auth.create_account') }}</a>
        </p>
    @endif
</div>
