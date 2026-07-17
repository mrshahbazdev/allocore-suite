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
        <h1 class="text-2xl font-bold text-slate-900">{{ __('auth.verify_title') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('auth.verify_subtitle') }}</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ __('auth.verification_sent') }}
        </div>
    @endif

    <div class="space-y-4">
        <x-primary-button wire:click="sendVerification" class="w-full justify-center rounded-lg bg-indigo-600 px-4 py-3 text-base font-semibold normal-case tracking-normal text-white shadow-sm hover:bg-indigo-700 focus:ring-indigo-500">
            {{ __('auth.resend_verification') }}
        </x-primary-button>

        <button wire:click="logout" type="button" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            {{ __('auth.log_out') }}
        </button>
    </div>
</div>
