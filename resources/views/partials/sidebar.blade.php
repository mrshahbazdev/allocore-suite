@php($user = auth()->user())
@php($isAdmin = $user?->isAdmin())
@php($accessibleModules = \App\Models\Module::where('is_active', true)->get()->filter(fn ($m) => $user?->hasModule($m->key)))
@php($moduleGroups = [
    'strategie' => ['north-star', 'vision-flow', 'nur-du', 'org-matrix'],
    'umsatz' => ['financial-platform', 'invoice-maker', 'cash-core', 'sweet-spot'],
    'ordnung' => ['plan-hive', 'time-butler', 'loop-engine', 'focus-matrix', 'sop-builder', 'knowledge-manager'],
    'einfluss' => ['keyword-cluster', 'lead-quality'],
])
@php($groupNames = [
    'strategie' => __('Strategie & Führung'),
    'umsatz' => __('Umsatz & Finanzen'),
    'ordnung' => __('Ordnung & Prozesse'),
    'einfluss' => __('Einfluss & Kunden'),
])

<nav class="flex h-full flex-col px-3 py-4 space-y-6 overflow-y-auto" x-data='{ groups: (() => { try { const s = localStorage.getItem("sidebarGroups"); return s ? JSON.parse(s) : {main:true,module:true,allocore:true,tools:true,insights:true,account:true,admin:false}; } catch (e) { return {main:true,module:true,allocore:true,tools:true,insights:true,account:true,admin:false}; } })() }' x-effect="localStorage.setItem('sidebarGroups', JSON.stringify(groups))">
    @if (request()->is('app/*'))
        @php($moduleSegment = request()->segment(2))
        @php($moduleSidebar = $moduleSegment ? \App\Models\Module::where('route_prefix', $moduleSegment)->where('is_active', true)->first() : null)
        @if ($moduleSidebar)
            <div>
                <button type="button" @click="groups.module = !groups.module" class="flex w-full items-center justify-between px-3 text-xs font-semibold uppercase tracking-wider text-slate-500 hover:text-slate-300">
                    <span>{{ $moduleSidebar->name }}</span>
                    <svg class="h-4 w-4" :class="groups.module ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="groups.module" class="mt-2 space-y-1">
                    @include('partials.active-module-nav', ['layout' => 'vertical'])
                </div>
            </div>
        @endif
    @endif

    @if (! request()->is('app/*'))
    {{-- Main --}}
    <div>
        <button type="button" @click="groups.main = !groups.main" class="flex w-full items-center justify-between px-3 text-xs font-semibold uppercase tracking-wider text-slate-500 hover:text-slate-300">
            <span>{{ __('Haupt') }}</span>
            <svg class="h-4 w-4" :class="groups.main ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        </button>
        <div x-show="groups.main" class="mt-2 space-y-1">
            <x-sidebar-link route="dashboard" icon="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z">{{ __('Dashboard') }}</x-sidebar-link>
            <x-sidebar-link route="dashboards.index" icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125Z">{{ __('My Dashboards') }}</x-sidebar-link>
            <x-sidebar-link route="workspace.index" icon="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM2.25 13.125c0-.621.504-1.125 1.125-1.125h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 7.125c0-.621.504-1.125 1.125-1.125h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 13.125c0-.621.504-1.125 1.125-1.125h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75z">{{ __('Workspace') }}</x-sidebar-link>
        </div>
    </div>

    {{-- Allocore --}}
    <div>
        <button type="button" @click="groups.allocore = !groups.allocore" class="flex w-full items-center justify-between px-3 text-xs font-semibold uppercase tracking-wider text-slate-500 hover:text-slate-300">
            <span>{{ __('Allocore') }}</span>
            <svg class="h-4 w-4" :class="groups.allocore ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        </button>
        <div x-show="groups.allocore"  class="mt-2 space-y-1">
            <x-sidebar-link route="allocore-score.index" icon="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.261 4.14L21 12.84M4.5 17.25h12.75m-12.75 0a1.5 1.5 0 01-1.5-1.5M4.5 17.25a1.5 1.5 0 01-1.5-1.5M17.25 4.5v11.25m0 0l1.5 1.5m-1.5-1.5l-1.5 1.5">{{ __('Allocore Score') }}</x-sidebar-link>
            <x-sidebar-link route="audit.index" icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.108v8.842a2.25 2.25 0 002.25 2.25z">{{ __('Audit') }}</x-sidebar-link>
            <x-sidebar-link route="recommendations.index" icon="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z">{{ __('Recommendations') }}</x-sidebar-link>
            <x-sidebar-link route="workflows.index" icon="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z">{{ __('Workflows') }}</x-sidebar-link>
        </div>
    </div>

    {{-- Tools --}}
    <div>
        <button type="button" @click="groups.tools = !groups.tools" class="flex w-full items-center justify-between px-3 text-xs font-semibold uppercase tracking-wider text-slate-500 hover:text-slate-300">
            <span>{{ __('Tools') }}</span>
            <svg class="h-4 w-4" :class="groups.tools ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        </button>
        <div x-show="groups.tools"  class="mt-2 space-y-1">
            <x-sidebar-link route="tools.index" icon="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655-5.226a2.984 2.984 0 00-.72-1.283 2.985 2.985 0 00-1.584-.859 3.004 3.004 0 00-1.747.186 2.948 2.948 0 00-1.178.84L1.5 12l2.755-2.795a4.984 4.984 0 012.105-1.35 4.995 4.995 0 012.91-.187c.929.18 1.78.595 2.43 1.167M6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z">{{ __('All Tools') }}</x-sidebar-link>
            <x-sidebar-link route="marketplace.index" icon="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z">{{ __('Marketplace') }}</x-sidebar-link>

            @foreach ($moduleGroups as $group => $keys)
                @php($groupModules = $accessibleModules->whereIn('key', $keys)->values())
                @if ($groupModules->isNotEmpty())
                    <p class="pt-2 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ $groupNames[$group] }}</p>
                    @foreach ($groupModules as $module)
                        <a href="{{ url('app/'.$module->route_prefix) }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->is('app/'.$module->route_prefix.'*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
                            <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                            <span>{{ $module->name }}</span>
                        </a>
                    @endforeach
                @endif
            @endforeach

            @php($otherModules = $accessibleModules->filter(fn ($m) => ! collect($moduleGroups)->flatten()->contains($m->key))->values())
            @if ($otherModules->isNotEmpty())
                <p class="pt-2 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('Weitere') }}</p>
                @foreach ($otherModules as $module)
                    <a href="{{ url('app/'.$module->route_prefix) }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->is('app/'.$module->route_prefix.'*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
                        <span class="h-2 w-2 rounded-full bg-slate-500"></span>
                        <span>{{ $module->name }}</span>
                    </a>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Insights --}}
    <div>
        <button type="button" @click="groups.insights = !groups.insights" class="flex w-full items-center justify-between px-3 text-xs font-semibold uppercase tracking-wider text-slate-500 hover:text-slate-300">
            <span>{{ __('Insights') }}</span>
            <svg class="h-4 w-4" :class="groups.insights ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        </button>
        <div x-show="groups.insights"  class="mt-2 space-y-1">
            <x-sidebar-link route="advisor.index" icon="M9.879 7.519c1.171-1.026 2.999-1.026 4.17 0 1.172 1.026 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z">{{ __('AI Advisor') }}</x-sidebar-link>
            <x-sidebar-link route="assistant.index" icon="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z">{{ __('AI Assistant') }}</x-sidebar-link>
            <x-sidebar-link route="search.index" icon="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z">{{ __('Search') }}</x-sidebar-link>
            <x-sidebar-link route="usage.index" icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z">{{ __('Usage Analytics') }}</x-sidebar-link>
            <x-sidebar-link route="timeline.index" icon="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z">{{ __('Activity Timeline') }}</x-sidebar-link>
            <x-sidebar-link route="alerts.index" icon="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.75A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.454 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0">{{ __('Alerts') }}</x-sidebar-link>
            <x-sidebar-link route="scheduled-reports.index" icon="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008z">{{ __('Scheduled Reports') }}</x-sidebar-link>
            <x-sidebar-link route="imports.index" icon="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3">{{ __('Bulk Import') }}</x-sidebar-link>
        </div>
    </div>

    {{-- Account --}}
    <div>
        <button type="button" @click="groups.account = !groups.account" class="flex w-full items-center justify-between px-3 text-xs font-semibold uppercase tracking-wider text-slate-500 hover:text-slate-300">
            <span>{{ __('Account') }}</span>
            <svg class="h-4 w-4" :class="groups.account ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        </button>
        <div x-show="groups.account"  class="mt-2 space-y-1">
            <x-sidebar-link route="billing.plans" icon="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z">{{ __('Plans & Pricing') }}</x-sidebar-link>
            <x-sidebar-link route="billing.subscriptions" icon="M9 12.75L11.25 15 15 9.75M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z">{{ __('My Subscriptions') }}</x-sidebar-link>
            <x-sidebar-link route="teams.index" icon="M18 18.72v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48c0-.66.34-1.26.88-1.61l.28-.17a.75.75 0 00.25-1.03l-.11-.18a3.75 3.75 0 00-6.5 0l-.11.18a.75.75 0 00.25 1.03l.28.17c.54.35.88.95.88 1.61v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48a2.25 2.25 0 00-3-2.12 2.25 2.25 0 00-1.5 2.12v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48a6.75 6.75 0 0111.25-5.07 6.75 6.75 0 019.75 5.07zM12 12a3 3 0 100-6 3 3 0 000 6z">{{ __('Teams') }}</x-sidebar-link>
            <x-sidebar-link route="profile" icon="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z">{{ __('Profile') }}</x-sidebar-link>
        </div>
    </div>

    @if ($isAdmin)
        {{-- Admin --}}
        <div>
            <button type="button" @click="groups.admin = !groups.admin" class="flex w-full items-center justify-between px-3 text-xs font-semibold uppercase tracking-wider text-amber-400 hover:text-amber-300">
                <span>{{ __('Administration') }}</span>
                <svg class="h-4 w-4" :class="groups.admin ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            </button>
            <div x-show="groups.admin"  class="mt-2 space-y-4">
                <div class="space-y-1">
                    <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('Management') }}</p>
                    <x-sidebar-link route="admin.index" icon="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z">{{ __('Admin Dashboard') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.users.index" icon="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.637-2.911M15 19.128V13.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 13.5v3.75m-3-1.837a6.375 6.375 0 0111.637-2.911c1.249 1.037 2.058 2.451 2.305 3.97">{{ __('Users') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.teams.index" icon="M18 18.72v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48c0-.66.34-1.26.88-1.61l.28-.17a.75.75 0 00.25-1.03l-.11-.18a3.75 3.75 0 00-6.5 0l-.11.18a.75.75 0 00.25 1.03l.28.17c.54.35.88.95.88 1.61v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48a2.25 2.25 0 00-3-2.12 2.25 2.25 0 00-1.5 2.12v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48a6.75 6.75 0 0111.25-5.07 6.75 6.75 0 019.75 5.07zM12 12a3 3 0 100-6 3 3 0 000 6z">{{ __('Teams') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.case-studies.index" icon="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.967 8.967 0 00-6 2.292m6-2.292v14.25m0-14.25A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25">{{ __('Case Studies') }}</x-sidebar-link>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('Catalog') }}</p>
                    <x-sidebar-link route="admin.modules.index" icon="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z">{{ __('Modules') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.plans.index" icon="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z">{{ __('Plans') }}</x-sidebar-link>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('Site & Content') }}</p>
                    <x-sidebar-link route="admin.setup.index" icon="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655-5.226a2.984 2.984 0 00-.72-1.283 2.985 2.985 0 00-1.584-.859 3.004 3.004 0 00-1.747.186 2.948 2.948 0 00-1.178.84L1.5 12l2.755-2.795a4.984 4.984 0 012.105-1.35 4.995 4.995 0 012.91-.187c.929.18 1.78.595 2.43 1.167M6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z">{{ __('Setup Wizard') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.appearance.index" icon="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 002.25 2.25h6.364a2.25 2.25 0 002.25-2.25 3 3 0 00-5.78-1.128 2.25 2.25 0 01-4.444 0zM12 2.25a6.75 6.75 0 00-6.75 6.75v.75c0 4.893 3.576 8.775 7.856 9.633.533.103 1.083.167 1.643.167h.059c.56 0 1.11-.064 1.643-.167 4.28-.858 7.856-4.74 7.856-9.633v-.75A6.75 6.75 0 0012 2.25z">{{ __('Appearance') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.landing.index" icon="M3.75 3A1.5 1.5 0 002.25 4.5v15A1.5 1.5 0 003.75 21h4.5a1.5 1.5 0 001.5-1.5v-15a1.5 1.5 0 00-1.5-1.5h-4.5zm9 0A1.5 1.5 0 0011.25 4.5v15a1.5 1.5 0 001.5 1.5h4.5a1.5 1.5 0 001.5-1.5v-15a1.5 1.5 0 00-1.5-1.5h-4.5z">{{ __('Landing Page') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.pages.index" icon="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.967 8.967 0 00-6 2.292m6-2.292v14.25m0-14.25A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25">{{ __('Pages') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.blog.posts.index" icon="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10">{{ __('Blog') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.glossary.index" icon="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.967 8.967 0 00-6 2.292m6-2.292v14.25m0-14.25A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25">{{ __('Glossary') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.industries.index" icon="M3.75 6A2.25 2.25 0 016 3.75h12A2.25 2.25 0 0120.25 6v12a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18V6zM6 6.75h12v10.5H6V6.75zm3 3v4.5m4.5-4.5v4.5">{{ __('Industries') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.announcements.index" icon="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5">{{ __('Announcements') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.media.index" icon="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v10.5a2.25 2.25 0 002.25 2.25z">{{ __('Media') }}</x-sidebar-link>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('Billing') }}</p>
                    <x-sidebar-link route="admin.billing.index" icon="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0l1.586-1.19a1.5 1.5 0 011.872.18l.893.893a1.5 1.5 0 01.18 1.872l-1.586 1.19a4.495 4.495 0 01-5.803 0l-.893-.893a1.5 1.5 0 01-.18-1.872l1.586-1.19zm0 0V4.5m0 0A1.5 1.5 0 0112 3a1.5 1.5 0 011.5 1.5V6m0 0V4.5m0 0A1.5 1.5 0 0112 3a1.5 1.5 0 00-1.5 1.5V6">{{ __('Billing Dashboard') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.subscriptions.index" icon="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z">{{ __('Subscriptions') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.invoices.index" icon="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z">{{ __('Invoices') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.payments.index" icon="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.029-5.22m5.029 5.22l.01.006l-.01-.006zM15 12a3 3 0 11-6 0 3 3 0 016 0z">{{ __('Payments') }}</x-sidebar-link>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('System') }}</p>
                    <x-sidebar-link route="admin.settings.index" icon="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.47 1.55l-1.107.415c-.355.133-.612.403-.74.72-.127.316-.19.66-.19 1.006v.283c0 .35.063.69.19 1.006.128.317.385.587.74.72l1.107.415a1.125 1.125 0 01.47 1.55l-1.296 2.247a1.125 1.125 0 01-1.37.49l-1.217-.456a1.515 1.515 0 00-1.075.124c-.073.044-.146.087-.22.127-.332.184-.582.496-.645.87l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.645-.87a3.725 3.725 0 01-.22-.127c-.325-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 01-1.37-.49l-1.296-2.247a1.125 1.125 0 01.47-1.55l1.107-.415c.355-.133.612-.403.74-.72.127-.316.19-.66.19-1.006v-.284c0-.349-.063-.69-.19-1.006a1.374 1.374 0 00-.74-.72l-1.107-.415a1.125 1.125 0 01-.47-1.55l1.296-2.247a1.125 1.125 0 011.37-.49l1.217.456c.355.133.75.072 1.076-.124.072-.044.145-.087.218-.127.332-.184.582-.496.645-.87l.213-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z">{{ __('Site Settings') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.mail-settings.index" icon="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5H4.5A2.25 2.25 0 002.25 6.75m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75">{{ __('Mail Settings') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.audits.index" icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.108v8.842a2.25 2.25 0 002.25 2.25z">{{ __('Audits') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.audits.pillar-questions.index" icon="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z">{{ __('Mini-Audit Questions') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.financial.index" icon="M12 6v12m-8.49-6.364l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0l.293.293a1 1 0 001.414 0l.293-.293a1 1 0 011.414 0">{{ __('Financial') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.thresholds.index" icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z">{{ __('KPI Thresholds') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.analytics.index" icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z">{{ __('Analytics') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.activity-logs.index" icon="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z">{{ __('Activity Logs') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.roles.index" icon="M18 18.72v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48c0-.66.34-1.26.88-1.61l.28-.17a.75.75 0 00.25-1.03l-.11-.18a3.75 3.75 0 00-6.5 0l-.11.18a.75.75 0 00.25 1.03l.28.17c.54.35.88.95.88 1.61v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48a2.25 2.25 0 00-3-2.12 2.25 2.25 0 00-1.5 2.12v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48a6.75 6.75 0 0111.25-5.07 6.75 6.75 0 019.75 5.07zM12 12a3 3 0 100-6 3 3 0 000 6z">{{ __('Roles') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.support-tickets.index" icon="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z">{{ __('Support Tickets') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.integrations.index" icon="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.055 4.055a1 1 0 01-1.414-1.414l4.055-4.055a2.5 2.5 0 10-3.536-3.536l-4.055 4.055a1 1 0 01-1.414-1.414l4.055-4.055a4.5 4.5 0 015.657-5.657l.707.707a1 1 0 01.293.707v3.536a1 1 0 01-1 1h-3.536a1 1 0 01-.707-.293l-.707-.707a1 1 0 010-1.414z">{{ __('Integrations') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.status-incidents.index" icon="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.624zM12 15.75h.007v.008H12v-.008z">{{ __('Status Incidents') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.log-viewer.index" icon="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z">{{ __('Logs') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.backups.index" icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.108v8.842a2.25 2.25 0 002.25 2.25z">{{ __('Backups') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.exports.index" icon="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3">{{ __('Exports') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.api-tokens.index" icon="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L12 21V3a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0124 3v9a2.25 2.25 0 01-2.25 2.25h-1.5a2.25 2.25 0 01-1.875-1.125l-.658-1.175a.75.75 0 00-1.281 0l-.658 1.175a2.25 2.25 0 01-1.875 1.125h-1.5A2.25 2.25 0 0112 12V3">{{ __('API Tokens') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.session-manager.index" icon="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z">{{ __('Sessions') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.maintenance.index" icon="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655-5.226a2.984 2.984 0 00-.72-1.283 2.985 2.985 0 00-1.584-.859 3.004 3.004 0 00-1.747.186 2.948 2.948 0 00-1.178.84L1.5 12l2.755-2.795a4.984 4.984 0 012.105-1.35 4.995 4.995 0 012.91-.187c.929.18 1.78.595 2.43 1.167M6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z">{{ __('Maintenance') }}</x-sidebar-link>
                    <x-sidebar-link route="admin.env.index" icon="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5">{{ __('Environment') }}</x-sidebar-link>
                </div>
            </div>
        </div>
    @endif
    @endif
</nav>
