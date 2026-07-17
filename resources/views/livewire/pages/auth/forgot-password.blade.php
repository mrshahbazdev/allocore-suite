<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-2xl font-bold text-slate-900">Reset your password</h1>
        <p class="mt-2 text-sm text-slate-500">We’ll send a secure link to your email so you can choose a new password.</p>
    </div>

    <x-auth-session-status class="mb-6 rounded-xl border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="email" id="email" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm" type="email" name="email" required autofocus placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('login') }}" wire:navigate class="text-center text-sm font-medium text-indigo-600 hover:text-indigo-500 hover:underline sm:text-left">{{ __('Back to log in') }}</a>
            <x-primary-button class="w-full justify-center rounded-xl bg-indigo-600 px-6 py-3 text-base font-semibold uppercase tracking-wide text-white shadow-lg hover:bg-indigo-500 focus:ring-indigo-500 sm:w-auto">
                {{ __('Send reset link') }}
            </x-primary-button>
        </div>
    </form>
</div>
