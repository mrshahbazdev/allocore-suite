<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-2xl font-bold text-slate-900">Set a new password</h1>
        <p class="mt-2 text-sm text-slate-500">Choose a strong password to secure your account.</p>
    </div>

    <x-auth-session-status class="mb-6 rounded-xl border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form wire:submit="resetPassword" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="email" id="email" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm" type="email" name="email" required autofocus autocomplete="username" placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New password')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="password" id="password" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm" type="password" name="password" required autocomplete="new-password" placeholder="Create a strong password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm new password')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="Repeat your new password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center rounded-xl bg-indigo-600 px-4 py-3 text-base font-semibold uppercase tracking-wide text-white shadow-lg hover:bg-indigo-500 focus:ring-indigo-500">
                {{ __('Reset password') }}
            </x-primary-button>
        </div>
    </form>
</div>
