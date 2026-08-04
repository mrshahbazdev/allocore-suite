    <style>
        @keyframes fadeUp {
            0% { opacity: 0; transform: translateY(1rem); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>

    {{-- Executive header --}}
    <div class="mb-8 rounded-2xl bg-[#ff9200] p-6 text-white shadow-lg lg:p-8 opacity-0 animate-fade-up" style="animation-delay: 0ms">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium opacity-90">{{ __('Welcome back') }}</p>
                <h1 class="text-3xl font-bold lg:text-4xl">{{ auth()->user()?->name }}</h1>
                <p class="mt-1 text-sm opacity-90">{{ __('Your Allocore workspace is ready.') }}</p>
            </div>
            @if ($allocoreScore)
                <div class="rounded-2xl border border-white/20 bg-white/10 p-6 backdrop-blur">
                    <p class="text-sm font-medium opacity-90">{{ __('Allocore Score') }}</p>
                    <div class="mt-2 flex items-end gap-3">
                        <span class="text-5xl font-bold lg:text-6xl">{{ $allocoreScore->score }}</span>
                        <span class="mb-2 rounded-full bg-white px-3 py-1 text-sm font-semibold text-[#ff9200]">{{ __($allocoreScore->maturity_level) }}</span>
                    </div>
                    <p class="mt-1 text-sm opacity-90">{{ __('out of 100') }}</p>
                </div>
            @endif
            <a href="{{ route('audit.index') }}" class="inline-flex items-center rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-[#ff9200] shadow-sm hover:bg-white/90">
                {{ __('Start audit') }}
            </a>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 opacity-0 animate-fade-up" style="animation-delay: 40ms">
        @php($actions = [
            ['label' => __('Start audit'), 'route' => 'audit.index', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-[#ff9200]'],
            ['label' => __('Analyze my tools'), 'route' => 'tool-analyzer.index', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'color' => 'bg-[#0094af]'],
            ['label' => __('Browse plans'), 'route' => 'billing.plans', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z', 'color' => 'bg-[#ff9200]'],
            ['label' => __('Invite team'), 'route' => 'teams.index', 'icon' => 'M15 19.128a9.38 9.38 0 002.598.957c.68.166 1.35-.194 1.55-.9.462-1.64.198-3.338-.8-4.575m-3.65-7.68a3 3 0 11-6 0 3 3 0 016 0zm6 7.5a6.75 6.75 0 00-9-6.15 6.75 6.75 0 00-9 6.15', 'color' => 'bg-[#0094af]'],
        ])
        @foreach ($actions as $action)
            <a href="{{ route($action['route']) }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $action['color'] }} text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $action['icon'] }}"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-900 group-hover:text-[#ff9200]">{{ $action['label'] }}</p>
                    <p class="text-xs text-slate-500">{{ __('Open') }}</p>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Pillar scores & recommendations --}}
    @if ($allocoreScore)
        <div class="mb-8 grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2 opacity-0 animate-fade-up" style="animation-delay: 80ms">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Pillar scores') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($allocoreScore->pillars as $pillar)
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ __($pillar['name']) }}</span>
                                <span class="font-semibold text-slate-900">{{ $pillar['score'] }}</span>
                            </div>
                            <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-[#ff9200]" style="width: {{ min(100, max(0, $pillar['score'])) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('allocore-score.index') }}" class="mt-4 inline-block text-sm font-semibold text-[#ff9200] hover:underline">{{ __('View score history') }}</a>
            </div>

            @include('partials.allocore-recommendations', ['recommendations' => $allocoreRecommendations])
        </div>
    @else
        <div class="mb-8 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600 opacity-0 animate-fade-up" style="animation-delay: 80ms">
            <p class="font-semibold">{{ __('Discover your Allocore Score') }}</p>
            <p class="mt-1 text-sm">{{ __('Run an AuditPro assessment to see where your company stands on the corporate needs pyramid.') }}</p>
            <a href="{{ route('audit.index') }}" class="mt-4 inline-block rounded-lg bg-[#ff9200] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">{{ __('Start audit') }}</a>
        </div>
    @endif

    {{-- Stats --}}
    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4 opacity-0 animate-fade-up" style="animation-delay: 120ms">
        @php($executiveStats = [
            ['label' => __('Active tools'), 'value' => $stats['active_modules'], 'color' => 'bg-[#ff9200]', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
            ['label' => __('Workspace members'), 'value' => $stats['workspace_members'], 'color' => 'bg-[#0094af]', 'icon' => 'M15 19.128a9.38 9.38 0 002.598.957c.68.166 1.35-.194 1.55-.9.462-1.64.198-3.338-.8-4.575m-3.65-7.68a3 3 0 11-6 0 3 3 0 016 0zm6 7.5a6.75 6.75 0 00-9-6.15 6.75 6.75 0 00-9 6.15'],
            ['label' => __('Recent activity'), 'value' => $stats['recent_activities'], 'color' => 'bg-[#ff9200]', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => __('Current plan'), 'value' => $subscription?->plan?->name ?? __('Free'), 'color' => 'bg-[#0094af]', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
        ])
        @foreach ($executiveStats as $stat)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stat['value'] }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $stat['color'] }} text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}"/></svg>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Active tools --}}
    <h2 class="mb-3 text-lg font-semibold text-slate-900 opacity-0 animate-fade-up" style="animation-delay: 160ms">{{ __('My Tools') }} ({{ $activeModules->count() }})</h2>
    @if ($activeModules->isEmpty())
        <div class="mb-8 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 opacity-0 animate-fade-up" style="animation-delay: 160ms">
            {{ __('You have no active tools yet.') }} <a href="{{ route('billing.plans') }}" class="text-[#ff9200] hover:underline">{{ __('Browse plans') }}</a>
        </div>
    @else
        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4 opacity-0 animate-fade-up" style="animation-delay: 160ms">
            @foreach ($activeModules as $module)
                <a href="{{ url('app/'.$module->route_prefix) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-[#ff9200] hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#ff9200] text-sm font-bold text-white">
                            {{ strtoupper(substr($module->name, 0, 1)) }}
                        </div>
                        <span class="text-[10px] rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 font-medium">{{ __('Active') }}</span>
                    </div>
                    <h3 class="mt-3 font-semibold text-slate-900 group-hover:text-[#ff9200]">{{ $module->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ Str::limit($module->description, 80) }}</p>
                    @if (isset($moduleStats[$module->key]['count']))
                        <p class="mt-2 text-xs text-slate-500"><span class="font-semibold text-slate-900">{{ $moduleStats[$module->key]['count'] }}</span> {{ $moduleStats[$module->key]['label'] }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    {{-- Recent activity --}}
    <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm opacity-0 animate-fade-up" style="animation-delay: 200ms">
        <h3 class="text-base font-semibold text-slate-900">{{ __('Recent activity') }}</h3>
        @if ($activityLogs->isEmpty())
            <p class="mt-4 text-sm text-slate-500">{{ __('No recent activity recorded.') }}</p>
        @else
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach ($activityLogs as $log)
                    <li class="py-3">
                        <p class="text-sm text-slate-900">{{ $log->description }}</p>
                        <p class="text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
