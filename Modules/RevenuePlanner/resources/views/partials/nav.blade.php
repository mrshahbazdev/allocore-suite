<nav class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-4 rounded-2xl shadow-sm mb-6" data-no-navigate>
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0094af] to-cyan-600 text-white font-black text-xl shadow-sm">
            📈
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-base font-bold text-slate-900">{{ __('ALLOCORE UMSATZPLANER') }}</h2>
                <span class="rounded-full bg-cyan-100 px-2.5 py-0.5 text-xs font-semibold text-cyan-800">v1.0</span>
            </div>
            <p class="text-xs text-slate-500">{{ __('Zielumsatz, Umsatzlücken-Radar & Neukunden-Trichter') }}</p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('revenueplanner.dashboard') }}" class="rounded-xl px-3.5 py-2 text-xs font-bold transition {{ request()->routeIs('revenueplanner.dashboard') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            📊 {{ __('Dashboard') }}
        </a>
        <a href="{{ route('revenueplanner.wizard') }}" class="rounded-xl px-3.5 py-2 text-xs font-bold transition {{ request()->routeIs('revenueplanner.wizard') ? 'bg-[#ff9200] text-white shadow-sm' : 'bg-orange-50 text-orange-700 hover:bg-orange-100' }}">
            ⚡ {{ __('Neuer Planungs-Wizard') }}
        </a>
    </div>
</nav>
