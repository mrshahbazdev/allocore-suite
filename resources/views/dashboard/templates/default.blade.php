    <style>
        @keyframes fadeUp {
            0% { opacity: 0; transform: translateY(1rem); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            0% { opacity: 0; transform: scale(0.95); }
            100% { opacity: 1; transform: scale(1); }
        }
        @keyframes slideInLeft {
            0% { opacity: 0; transform: translateX(-1rem); }
            100% { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeUpDim {
            0% { opacity: 0; transform: translateY(1rem); }
            100% { opacity: 0.9; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-scale-in { animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-slide-in-left { animation: slideInLeft 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-fade-up-dim { animation: fadeUpDim 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>

    {{-- Announcements --}}
    @if ($announcements->isNotEmpty())
        <div class="mb-6 space-y-3">
            @foreach ($announcements as $announcement)
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-900 opacity-0 animate-slide-in-left" style="animation-delay: {{ $loop->index * 80 }}ms">
                    <h2 class="font-semibold">{{ $announcement->title }}</h2>
                    <p class="mt-1 text-sm text-blue-800">{{ $announcement->body }}</p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Welcome header --}}
    <div class="mb-6 rounded-2xl bg-[#ff9200] p-6 text-white shadow-lg lg:p-8 opacity-0 animate-fade-up" style="animation-delay: 0ms">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium opacity-90">{{ __('Welcome back') }}</p>
                <h1 class="text-2xl font-bold lg:text-3xl">{{ auth()->user()?->name }}</h1>
                <p class="mt-1 text-sm opacity-90">{{ __('Your Allocore workspace is ready.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('tool-analyzer.index') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-[#ff9200] shadow-sm hover:bg-white/90">{{ __('Analyze my tools') }}</a>
                @if ($activeModules->isNotEmpty())
                    <a href="{{ route('dashboard.export.pdf') }}" class="inline-flex items-center rounded-lg border border-white/30 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20">{{ __('Download PDF') }}</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick search + next steps --}}
    <div class="mb-6 grid gap-4 lg:grid-cols-3 opacity-0 animate-fade-up" style="animation-delay: 40ms">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <form action="{{ route('search.index') }}" method="get" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search') }}..." class="w-full rounded-lg border border-slate-200 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#ff9200] focus:outline-none focus:ring-2 focus:ring-[#ff9200]/20">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </div>
                <button type="submit" class="rounded-lg bg-[#ff9200] px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90">{{ __('Search') }}</button>
            </form>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-900">{{ __('Next steps') }}</p>
            <ul class="mt-3 space-y-2 text-sm">
                @if (! $allocoreScore)
                    <li><a href="{{ route('audit.index') }}" class="font-medium text-[#ff9200] hover:underline">{{ __('Start audit') }}</a></li>
                @endif
                @if ($activeModules->isEmpty())
                    <li><a href="{{ route('billing.plans') }}" class="font-medium text-[#0094af] hover:underline">{{ __('Browse plans') }}</a></li>
                @endif
                <li><a href="{{ route('teams.index') }}" class="font-medium text-[#0094af] hover:underline">{{ __('Invite a team member') }}</a></li>
            </ul>
        </div>
    </div>

    {{-- Allocore score --}}
    @if ($allocoreScore)
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8 opacity-0 animate-fade-up" style="animation-delay: 80ms">
            <div class="flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium uppercase tracking-wider text-slate-500">{{ __('Allocore Score') }}</p>
                    <div class="mt-2 flex items-end gap-3">
                        <span class="text-5xl font-extrabold text-slate-900 lg:text-6xl">{{ $allocoreScore->score }}</span>
                        <span class="mb-2 rounded-full px-3 py-1 text-sm font-semibold
                            {{ match($allocoreScore->maturity_level) { 'Excellent' => 'bg-emerald-100 text-emerald-700', 'Strong' => 'bg-green-100 text-green-700', 'Solid' => 'bg-blue-100 text-blue-700', 'Weak' => 'bg-amber-100 text-amber-700', default => 'bg-red-100 text-red-700' } }}">
                            {{ __($allocoreScore->maturity_level) }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ __('out of 100') }} &middot; {{ $allocoreScore->calculated_at->diffForHumans() }}</p>
                </div>
                <div class="w-full max-w-2xl lg:w-2/3">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
            </div>

            @include('partials.allocore-recommendations', ['recommendations' => $allocoreRecommendations])
        </div>
    @else
        <div class="mb-6 rounded-xl border border-dashed border-slate-300 bg-white p-6 text-slate-600 opacity-0 animate-fade-up" style="animation-delay: 80ms">
            <p class="font-semibold">{{ __('Discover your Allocore Score') }}</p>
            <p class="mt-1 text-sm">{{ __('Run an AuditPro assessment to see where your company stands on the corporate needs pyramid.') }}</p>
            <a href="{{ route('audit.index') }}" class="mt-3 inline-block rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">{{ __('Start audit') }}</a>
        </div>
    @endif

    {{-- Stats cards --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: 120ms">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Active tools') }}</p>
                <svg class="h-5 w-5 text-[#ff9200]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['active_modules'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: 160ms">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Locked add-ons') }}</p>
                <svg class="h-5 w-5 text-[#0094af]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 0h10.5a.75.75 0 01.75.75v8.25a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75V11.25a.75.75 0 01.75-.75z"/></svg>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['locked_modules'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: 200ms">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Workspace members') }}</p>
                <svg class="h-5 w-5 text-[#ff9200]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.598.957c.68.166 1.35-.194 1.55-.9.462-1.64.198-3.338-.8-4.575m-3.65-7.68a3 3 0 11-6 0 3 3 0 016 0zm6 7.5a6.75 6.75 0 00-9-6.15 6.75 6.75 0 00-9 6.15"/></svg>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['workspace_members'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: 240ms">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Recent activity') }}</p>
                <svg class="h-5 w-5 text-[#0094af]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['recent_activities'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: 280ms">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Current plan') }}</p>
                <svg class="h-5 w-5 text-[#ff9200]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
            </div>
            <p class="mt-2 text-lg font-bold text-slate-900 truncate">{{ $subscription?->plan?->name ?? __('Free') }}</p>
            @if ($subscription?->ends_at)
                <p class="text-xs text-slate-500">{{ __('Renews') }} {{ $subscription->ends_at->diffForHumans() }}</p>
            @endif
        </div>
    </div>

    {{-- Chart + history --}}
    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1 opacity-0 animate-fade-up" style="animation-delay: 320ms">
            <h3 class="text-base font-semibold text-slate-900">{{ __('Tool usage') }}</h3>
            <div class="mt-4 h-48">
                <canvas id="moduleUsageChart"></canvas>
            </div>
            @php($chartModules = collect($moduleStats)->filter(fn($v) => $v['accessible'])->values())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const ctx = document.getElementById('moduleUsageChart');
                    if (!ctx || typeof window.Chart === 'undefined') return;
                    new window.Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($chartModules->pluck('name')) !!},
                            datasets: [{
                                label: "{{ __('Records') }}",
                                data: {!! json_encode($chartModules->pluck('count')) !!},
                                backgroundColor: '#ff9200',
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } },
                                x: { ticks: { display: false } }
                            }
                        }
                    });
                });
            </script>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2 opacity-0 animate-fade-up" style="animation-delay: 360ms">
            <h3 class="text-base font-semibold text-slate-900">{{ __('Allocore history') }}</h3>
            @if ($allocoreHistory && count($allocoreHistory))
                @php($history = collect($allocoreHistory))
                <div class="mt-4 h-48">
                    <canvas id="allocoreHistoryChart"></canvas>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const ctx = document.getElementById('allocoreHistoryChart');
                        if (!ctx || typeof window.Chart === 'undefined') return;
                        new window.Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: {!! json_encode($history->pluck('date')->map(fn($d) => \Illuminate\Support\Carbon::parse($d)->format('M d'))) !!},
                                datasets: [{
                                    label: "{{ __('Score') }}",
                                    data: {!! json_encode($history->pluck('score')) !!},
                                    borderColor: '#0094af',
                                    backgroundColor: 'rgba(0,148,175,0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#ff9200',
                                    borderWidth: 2,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, max: 100 },
                                    x: { ticks: { display: false } }
                                }
                            }
                        });
                    });
                </script>
            @else
                <p class="mt-4 text-sm text-slate-500">{{ __('No score history yet.') }}</p>
            @endif
        </div>
    </div>

    {{-- Active tools --}}
    <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('My Tools') }} ({{ $activeModules->count() }})</h2>
    @if ($activeModules->isEmpty())
        <div class="mb-8 rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
            {{ __('You have no active tools yet.') }} <a href="{{ route('billing.plans') }}" class="text-[#ff9200] hover:underline">{{ __('Browse plans') }}</a>
        </div>
    @else
        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($activeModules as $module)
                <a href="{{ url('app/'.$module->route_prefix) }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-scale-in transition hover:-translate-y-1 hover:border-[#ff9200] hover:shadow-md" style="animation-delay: {{ ($loop->index * 60) + 400 }}ms">
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
                    <div class="mt-4 flex items-center text-sm font-semibold text-[#ff9200]">
                        {{ __('Open tool') }}
                        <svg class="ml-1 h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Locked tools --}}
    @if ($lockedModules->isNotEmpty())
        <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('Available add-ons') }}</h2>
        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($lockedModules as $module)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up-dim transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: {{ ($loop->index * 60) + 500 }}ms">
                    <div class="flex items-start justify-between">
                        <h3 class="font-semibold text-slate-900">{{ $module->name }}</h3>
                        <span class="text-[10px] rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 font-medium">{{ __('Locked') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ Str::limit($module->description, 80) }}</p>
                    <div class="mt-4">
                        <a href="{{ route('billing.plans', ['module' => $module->key]) }}" class="inline-flex items-center rounded-lg border border-[#ff9200] px-3 py-1.5 text-sm font-medium text-[#ff9200] hover:bg-indigo-50">{{ __('Subscribe') }}</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Recent activity --}}
    <div class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm opacity-0 animate-fade-up" style="animation-delay: 400ms">
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

    {{-- Module analytics widgets --}}
    @if (count($widgets))
        <h2 class="mb-4 text-lg font-semibold text-slate-900 opacity-0 animate-fade-up" style="animation-delay: 440ms">{{ __('Tool Analytics') }}</h2>
        <div class="mb-8 grid gap-4 lg:grid-cols-2 opacity-0 animate-fade-up" style="animation-delay: 460ms">
            @foreach ($widgets as $moduleKey => $widgetView)
                @include($widgetView)
            @endforeach
        </div>
    @endif
