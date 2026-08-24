@php($currentLocale = app()->getLocale())
@php($user = auth()->user())
@php($currentTeam = $user?->currentTeam)
@php($teams = $user?->teams()->get() ?? collect())

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-slate-100" aria-label="{{ __('Account menu') }}">
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0094af] text-xs font-bold text-white">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <span class="hidden text-sm font-medium text-slate-700 sm:inline">{{ $user->name }}</span>
        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
    </button>

    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 z-50 mt-2 w-60 rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
        <div class="px-4 py-2 text-sm font-semibold text-slate-900">{{ $user->name }}</div>
        <div class="px-4 pb-2 text-xs text-slate-500">{{ $user->email }}</div>

        <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">{{ __('Profile') }}</a>
        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">{{ __('Dashboard') }}</a>

        <div class="my-1 border-t border-slate-100"></div>
        <div class="px-4 py-1.5 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Companies') }}</div>
        @foreach ($teams as $team)
            <form method="POST" action="{{ route('teams.switch', $team) }}" class="block">
                @csrf
                <button type="submit" class="flex w-full items-center justify-between px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">
                    <span class="truncate">{{ $team->name }}</span>
                    @if ($currentTeam && $currentTeam->id === $team->id)
                        <span class="ml-2 rounded-full bg-[#ff9200]/10 px-2 py-0.5 text-[10px] font-medium text-[#ff9200]">{{ __('Current') }}</span>
                    @endif
                </button>
            </form>
        @endforeach
        <a href="{{ route('teams.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">{{ __('Manage companies') }}</a>

        <div class="my-1 border-t border-slate-100"></div>
        <div class="px-4 py-1.5 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Language') }}</div>
        <div class="grid grid-cols-2 gap-1 px-3 pb-2">
            @foreach (config('app.available_locales', ['en']) as $locale)
                <a href="{{ route('language', ['locale' => $locale]) }}" class="rounded-md px-2 py-1.5 text-center text-xs font-medium transition {{ $currentLocale === $locale ? 'bg-[#ff9200]/10 text-[#ff9200]' : 'text-slate-600 hover:bg-slate-50' }}">
                    {{ config('app.locale_names.'.$locale, strtoupper($locale)) }}
                </a>
            @endforeach
        </div>

        <div class="my-1 border-t border-slate-100"></div>
        <form method="POST" action="{{ route('logout') }}" class="block">
            @csrf
            <button type="submit" class="block w-full px-4 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">{{ __('Log out') }}</button>
        </form>
    </div>
</div>
