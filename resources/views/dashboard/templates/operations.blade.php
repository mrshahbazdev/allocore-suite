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

    {{-- Header --}}
    <div class="mb-6 rounded-2xl bg-[#0094af] p-6 text-white shadow-lg lg:p-8 opacity-0 animate-fade-up" style="animation-delay: 0ms">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium opacity-90">{{ __('Operations center') }}</p>
                <h1 class="text-2xl font-bold lg:text-3xl">{{ auth()->user()?->name }}</h1>
                <p class="mt-1 text-sm opacity-90">{{ __('Track activity, usage and tool health.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('workspace.index') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-[#0094af] shadow-sm hover:bg-white/90">{{ __('Open workspace') }}</a>
                <a href="{{ route('billing.plans') }}" class="inline-flex items-center rounded-lg border border-white/30 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20">{{ __('Browse plans') }}</a>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: 40ms">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Active tools') }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['active_modules'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: 80ms">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Locked add-ons') }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['locked_modules'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: 120ms">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Workspace members') }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['workspace_members'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: 160ms">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Recent activity') }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['recent_activities'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: 200ms">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Current plan') }}</p>
            <p class="mt-2 text-lg font-bold text-slate-900 truncate">{{ $subscription?->plan?->name ?? __('Free') }}</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1 opacity-0 animate-fade-up" style="animation-delay: 240ms">
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
                                backgroundColor: '#0094af',
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

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2 opacity-0 animate-fade-up" style="animation-delay: 280ms">
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
                                    borderColor: '#ff9200',
                                    backgroundColor: 'rgba(255,146,0,0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#0094af',
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

    {{-- Active tools + recent activity --}}
    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('My Tools') }} ({{ $activeModules->count() }})</h2>
            @if ($activeModules->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
                    {{ __('You have no active tools yet.') }} <a href="{{ route('billing.plans') }}" class="text-[#0094af] hover:underline">{{ __('Browse plans') }}</a>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($activeModules as $module)
                        <a href="{{ url('app/'.$module->route_prefix) }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-scale-in transition hover:-translate-y-1 hover:border-[#0094af] hover:shadow-md" style="animation-delay: {{ ($loop->index * 60) + 320 }}ms">
                            <div class="flex items-start justify-between">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#0094af] text-sm font-bold text-white">
                                    {{ strtoupper(substr($module->name, 0, 1)) }}
                                </div>
                                <span class="text-[10px] rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 font-medium">{{ __('Active') }}</span>
                            </div>
                            <h3 class="mt-3 font-semibold text-slate-900 group-hover:text-[#0094af]">{{ $module->name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ Str::limit($module->description, 80) }}</p>
                            @if (isset($moduleStats[$module->key]['count']))
                                <p class="mt-2 text-xs text-slate-500"><span class="font-semibold text-slate-900">{{ $moduleStats[$module->key]['count'] }}</span> {{ $moduleStats[$module->key]['label'] }}</p>
                            @endif
                            <div class="mt-4 flex items-center text-sm font-semibold text-[#0094af]">
                                {{ __('Open tool') }}
                                <svg class="ml-1 h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm opacity-0 animate-fade-up" style="animation-delay: 360ms">
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
    </div>

    {{-- Locked tools --}}
    @if ($lockedModules->isNotEmpty())
        <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('Available add-ons') }}</h2>
        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($lockedModules as $module)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm opacity-0 animate-fade-up-dim transition duration-200 hover:-translate-y-1 hover:shadow-md" style="animation-delay: {{ ($loop->index * 60) + 420 }}ms">
                    <div class="flex items-start justify-between">
                        <h3 class="font-semibold text-slate-900">{{ $module->name }}</h3>
                        <span class="text-[10px] rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 font-medium">{{ __('Locked') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ Str::limit($module->description, 80) }}</p>
                    <div class="mt-4">
                        <a href="{{ route('billing.plans', ['module' => $module->key]) }}" class="inline-flex items-center rounded-lg border border-[#0094af] px-3 py-1.5 text-sm font-medium text-[#0094af] hover:bg-cyan-50">{{ __('Subscribe') }}</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Module analytics widgets --}}
    @if (count($widgets))
        <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Tool Analytics') }}</h2>
        <div class="mb-8 grid gap-4 lg:grid-cols-2">
            @foreach ($widgets as $moduleKey => $widgetView)
                @include($widgetView)
            @endforeach
        </div>
    @endif
