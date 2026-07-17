<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">{{ __('profile.title') }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <aside class="lg:col-span-1">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col items-center text-center">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-2xl font-bold text-indigo-600">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>

                            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ auth()->user()->name }}</h3>
                            <p class="text-sm text-slate-500">{{ auth()->user()->email }}</p>

                            <div class="mt-4 w-full border-t border-slate-100 pt-4">
                                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('profile.member_since') }}</p>
                                <p class="mt-1 text-sm text-slate-700">{{ auth()->user()->created_at->format('F Y') }}</p>
                            </div>

                            @if (auth()->user()->currentTeam)
                                <div class="mt-4 w-full border-t border-slate-100 pt-4">
                                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('profile.current_team') }}</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ auth()->user()->currentTeam->name }}</p>
                                </div>
                            @endif

                            <div class="mt-4 w-full border-t border-slate-100 pt-4">
                                <a href="{{ route('two-factor.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Two-Factor Authentication') }}</a>
                            </div>

                            <div class="mt-2 w-full pt-2">
                                <a href="{{ route('profile.api-tokens.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('API Tokens') }}</a>
                            </div>

                            <div class="mt-2 w-full pt-2">
                                <a href="{{ route('profile.activity') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Account Activity') }}</a>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="space-y-6 lg:col-span-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <livewire:profile.update-profile-information-form />
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <livewire:profile.update-password-form />
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <livewire:profile.update-mail-settings-form />
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-rose-100 bg-rose-50/30 p-6 shadow-sm">
                        <livewire:profile.delete-user-form />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
