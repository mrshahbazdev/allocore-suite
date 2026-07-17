<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-2xl font-bold text-slate-900">Verify your email</h1>
        <p class="mt-2 text-sm text-slate-500">We sent a verification link to your email. Click it to finish setting up your account.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="space-y-4">
        <x-primary-button wire:click="sendVerification" class="w-full justify-center rounded-xl bg-indigo-600 px-4 py-3 text-base font-semibold uppercase tracking-wide text-white shadow-lg hover:bg-indigo-500 focus:ring-indigo-500">
            {{ __('Resend verification email') }}
        </x-primary-button>

        <button wire:click="logout" type="button" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            {{ __('Log out') }}
        </button>
    </div>
</div>
