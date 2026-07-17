<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-rose-900">{{ __('profile.delete.title') }}</h2>
        <p class="mt-1 text-sm text-slate-600">{{ __('profile.delete.description') }}</p>
    </header>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600"
    >
        {{ __('profile.delete.button') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6">
            <h2 class="text-lg font-semibold text-slate-900">
                {{ __('profile.delete.modal.title') }}
            </h2>

            <p class="mt-2 text-sm text-slate-600">
                {{ __('profile.delete.modal.description') }}
            </p>

            <div class="mt-6">
                <label for="delete_user_password" class="sr-only block text-sm font-medium text-slate-700">{{ __('profile.delete.password') }}</label>

                <input
                    wire:model="password"
                    id="delete_user_password"
                    name="password"
                    type="password"
                    class="block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                    placeholder="{{ __('profile.delete.password') }}"
                >

                @error('password')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    {{ __('profile.delete.cancel') }}
                </button>

                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                    {{ __('profile.delete.confirm') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
