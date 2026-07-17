<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-2xl font-bold text-slate-900">Create your account</h1>
        <p class="mt-2 text-sm text-slate-500">Set up your workspace and start with the modules you subscribe to.</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <div>
            <x-input-label for="name" :value="__('Full name')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="name" id="name" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm" type="text" name="name" required autofocus autocomplete="name" placeholder="Jane Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="email" id="email" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm" type="email" name="email" required autocomplete="username" placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="password" id="password" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="Create a strong password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" class="text-sm font-medium text-slate-700" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="Repeat your password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center rounded-xl bg-indigo-600 px-4 py-3 text-base font-semibold uppercase tracking-wide text-white shadow-lg hover:bg-indigo-500 focus:ring-indigo-500">
                {{ __('Create account') }}
            </x-primary-button>
        </div>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        {{ __('Already registered?') }}
        <a href="{{ route('login') }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline">{{ __('Log in') }}</a>
    </p>
</div>
