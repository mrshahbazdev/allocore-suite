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
        <h1 class="text-2xl font-bold text-slate-900">{{ __('auth.reset_title') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('auth.reset_subtitle') }}</p>
    </div>

    <x-auth-session-status class="mb-6 rounded-lg border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('auth.email')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="email" id="email" class="mt-2 block w-full rounded-lg border-slate-300 px-4 py-3 shadow-sm" type="email" name="email" required autofocus placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('login') }}" wire:navigate class="text-center text-sm font-medium text-indigo-600 hover:text-indigo-500 hover:underline sm:text-left">{{ __('auth.back_to_login') }}</a>
            <x-primary-button class="w-full justify-center rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold normal-case tracking-normal text-white shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 sm:w-auto">
                {{ __('auth.send_reset_link') }}
            </x-primary-button>
        </div>
    </form>
</div>
