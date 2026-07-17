<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-2xl font-bold text-slate-900">Confirm your password</h1>
        <p class="mt-2 text-sm text-slate-500">Please verify your password before continuing to a secure area.</p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-5">
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="password" id="password" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end pt-2">
            <x-primary-button class="w-full justify-center rounded-xl bg-indigo-600 px-6 py-3 text-base font-semibold uppercase tracking-wide text-white shadow-lg hover:bg-indigo-500 focus:ring-indigo-500 sm:w-auto">
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</div>
