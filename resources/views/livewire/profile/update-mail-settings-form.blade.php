<?php

use App\Models\MailSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $driver = 'smtp';
    public string $host = '';
    public int $port = 587;
    public string $username = '';
    public string $password = '';
    public string $encryption = 'none';
    public string $from_address = '';
    public string $from_name = '';

    public function mount(): void
    {
        $setting = Auth::user()->mailSetting;

        if ($setting) {
            $this->driver = $setting->driver ?? 'smtp';
            $this->host = $setting->host ?? '';
            $this->port = $setting->port ?? 587;
            $this->username = $setting->username ?? '';
            $this->encryption = $setting->encryption ?? 'none';
            $this->from_address = $setting->from_address ?? '';
            $this->from_name = $setting->from_name ?? '';
        }
    }

    public function updateMailSettings(): void
    {
        $validated = $this->validate([
            'driver' => 'required|string|max:50',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:1000',
            'encryption' => 'nullable|string|in:tls,ssl,none',
            'from_address' => 'required|email|max:255',
            'from_name' => 'nullable|string|max:255',
        ]);

        $data = [
            'driver' => $validated['driver'],
            'host' => $validated['host'],
            'port' => $validated['port'],
            'username' => $validated['username'],
            'encryption' => $validated['encryption'] === 'none' ? null : $validated['encryption'],
            'from_address' => $validated['from_address'],
            'from_name' => $validated['from_name'],
        ];

        if (filled($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        Auth::user()->mailSetting()->updateOrCreate(
            ['user_id' => Auth::id()],
            $data
        );

        $this->dispatch('mail-settings-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-semibold text-slate-900">{{ __('profile.mail.title') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('profile.mail.description') }}</p>
    </header>

    @php($global = \App\Models\MailSetting::query()->global()->first())
    @if (! Auth::user()->mailSetting?->isUsable() && $global && $global->isUsable())
        <div class="mt-4 rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
            {{ __('profile.mail.using_default') }}
        </div>
    @endif

    <form wire:submit="updateMailSettings" class="mt-6 grid gap-6 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('mail.driver') }}</label>
            <select wire:model="driver" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="smtp">SMTP</option>
            </select>
            @error('driver')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('mail.host') }}</label>
            <input wire:model="host" type="text" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @error('host')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('mail.port') }}</label>
            <input wire:model="port" type="number" min="1" max="65535" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @error('port')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('mail.encryption') }}</label>
            <select wire:model="encryption" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="none">{{ __('mail.encryption_none') }}</option>
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
            </select>
            @error('encryption')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('mail.username') }}</label>
            <input wire:model="username" type="text" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @error('username')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('mail.password') }}</label>
            <input wire:model="password" type="password" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ Auth::user()->mailSetting?->password ? __('mail.password_unchanged') : '' }}">
            @if (Auth::user()->mailSetting?->password)
                <p class="mt-1 text-xs text-slate-500">{{ __('mail.password_unchanged') }}</p>
            @endif
            @error('password')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('mail.from_address') }}</label>
            <input wire:model="from_address" type="email" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @error('from_address')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('mail.from_name') }}</label>
            <input wire:model="from_name" type="text" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('from_name')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2 flex items-center gap-4">
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                {{ __('profile.save') }}
            </button>

            <x-action-message on="mail-settings-updated">
                {{ __('profile.saved') }}
            </x-action-message>
        </div>
    </form>
</section>
