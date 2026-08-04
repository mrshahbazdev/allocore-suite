    <style>
        @keyframes fadeUp {
            0% { opacity: 0; transform: translateY(1rem); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            0% { opacity: 0; transform: scale(0.95); }
            100% { opacity: 1; transform: scale(1); }
        }
        .animate-fade-up { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-scale-in { animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>

    {{-- Welcome --}}
    <div class="mb-8 rounded-2xl bg-[#ff9200] p-8 text-center text-white shadow-lg opacity-0 animate-fade-up" style="animation-delay: 0ms">
        <p class="text-sm font-medium opacity-90">{{ __('Welcome back') }}</p>
        <h1 class="mt-1 text-3xl font-bold lg:text-4xl">{{ auth()->user()?->name }}</h1>
        <p class="mx-auto mt-2 max-w-xl text-sm opacity-90">{{ __('Your Allocore workspace is ready.') }}</p>
    </div>

    {{-- Search --}}
    <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm opacity-0 animate-fade-up" style="animation-delay: 40ms">
        <form action="{{ route('search.index') }}" method="get" class="flex items-center gap-3">
            <div class="relative flex-1">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search') }}..." class="w-full rounded-xl border border-slate-200 py-3 pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#ff9200] focus:outline-none focus:ring-2 focus:ring-[#ff9200]/20">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </div>
            <button type="submit" class="rounded-xl bg-[#ff9200] px-6 py-3 text-sm font-semibold text-white hover:opacity-90">{{ __('Search') }}</button>
        </form>
    </div>

    {{-- Quick links --}}
    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 opacity-0 animate-fade-up" style="animation-delay: 80ms">
        @php($links = [
            ['label' => __('Workspace'), 'route' => 'workspace.index', 'color' => 'bg-[#ff9200]'],
            ['label' => __('Start audit'), 'route' => 'audit.index', 'color' => 'bg-[#0094af]'],
            ['label' => __('Browse plans'), 'route' => 'billing.plans', 'color' => 'bg-[#ff9200]'],
            ['label' => __('Settings'), 'route' => 'profile.show', 'color' => 'bg-[#0094af]'],
        ])
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}" class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full {{ $link['color'] }} text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </div>
                <p class="mt-3 text-sm font-semibold text-slate-900">{{ $link['label'] }}</p>
            </a>
        @endforeach
    </div>

    {{-- Allocore score --}}
    @if ($allocoreScore)
        <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm opacity-0 animate-fade-up" style="animation-delay: 120ms">
            <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ __('Allocore Score') }}</p>
                    <div class="mt-1 flex items-end gap-3">
                        <span class="text-5xl font-extrabold text-slate-900">{{ $allocoreScore->score }}</span>
                        <span class="mb-2 rounded-full px-3 py-1 text-sm font-semibold
                            {{ match($allocoreScore->maturity_level) { 'Excellent' => 'bg-emerald-100 text-emerald-700', 'Strong' => 'bg-green-100 text-green-700', 'Solid' => 'bg-blue-100 text-blue-700', 'Weak' => 'bg-amber-100 text-amber-700', default => 'bg-red-100 text-red-700' } }}">
                            {{ __($allocoreScore->maturity_level) }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('allocore-score.index') }}" class="text-sm font-semibold text-[#ff9200] hover:underline">{{ __('View score history') }}</a>
            </div>
        </div>
    @endif

    {{-- Active tools --}}
    <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('My Tools') }}</h2>
    @if ($activeModules->isEmpty())
        <div class="mb-8 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
            {{ __('You have no active tools yet.') }} <a href="{{ route('billing.plans') }}" class="text-[#ff9200] hover:underline">{{ __('Browse plans') }}</a>
        </div>
    @else
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($activeModules as $module)
                <a href="{{ url('app/'.$module->route_prefix) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-scale-in transition hover:-translate-y-1 hover:border-[#ff9200] hover:shadow-md" style="animation-delay: {{ ($loop->index * 60) + 160 }}ms">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#ff9200] text-sm font-bold text-white">
                        {{ strtoupper(substr($module->name, 0, 1)) }}
                    </div>
                    <h3 class="mt-3 font-semibold text-slate-900 group-hover:text-[#ff9200]">{{ $module->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ Str::limit($module->description, 80) }}</p>
                </a>
            @endforeach
        </div>
    @endif
