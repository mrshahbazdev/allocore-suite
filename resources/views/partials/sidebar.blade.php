@php($user = auth()->user())
@php($isAdmin = $user?->isAdmin())
@php($accessibleModules = \App\Models\Module::where('is_active', true)->get()->filter(fn ($m) => $user?->hasModule($m->key)))

<nav class="flex h-full flex-col px-3 py-4 space-y-5 overflow-y-auto">
    @if (request()->is('app/*'))
        @php($moduleSegment = request()->segment(2))
        @php($moduleSidebar = $moduleSegment ? \App\Models\Module::where('route_prefix', $moduleSegment)->where('is_active', true)->first() : null)
        @if ($moduleSidebar)
            <div class="rounded-xl bg-slate-800/50 p-3">
                <p class="mb-2 px-2 text-sm font-semibold text-white">{{ $moduleSidebar->name }}</p>
                @include('partials.active-module-nav', ['layout' => 'vertical'])
            </div>
        @endif
    @endif

    <div>
        <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Overview') }}</p>
        <div class="mt-1.5 space-y-1">
            <x-sidebar-link route="dashboard" icon="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z">{{ __('Dashboard') }}</x-sidebar-link>
            <x-sidebar-link route="allocore-score.index" icon="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.261 4.14L21 12.84M4.5 17.25h12.75m-12.75 0a1.5 1.5 0 01-1.5-1.5M4.5 17.25a1.5 1.5 0 01-1.5-1.5M17.25 4.5v11.25m0 0l1.5 1.5m-1.5-1.5l-1.5 1.5">{{ __('Allocore Score') }}</x-sidebar-link>
        </div>
    </div>

    <div>
        <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Allocore') }}</p>
        <div class="mt-1.5 space-y-1">
            <x-sidebar-link route="audit.index" icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.108v8.842a2.25 2.25 0 002.25 2.25z">{{ __('Audit') }}</x-sidebar-link>
            <x-sidebar-link route="recommendations.index" icon="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z">{{ __('Recommendations') }}</x-sidebar-link>
            <x-sidebar-link route="advisor.index" icon="M9.879 7.519c1.171-1.026 2.999-1.026 4.17 0 1.172 1.026 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z">{{ __('AI Advisor') }}</x-sidebar-link>
            <x-sidebar-link route="search.index" icon="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z">{{ __('Search') }}</x-sidebar-link>
            <x-sidebar-link route="scheduled-reports.index" icon="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008z">{{ __('Scheduled Reports') }}</x-sidebar-link>
        </div>
    </div>

    <div>
        <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Tools') }}</p>
        <div class="mt-1.5 space-y-1">
            <x-sidebar-link route="tools.index" icon="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655-5.226a2.984 2.984 0 00-.72-1.283 2.985 2.985 0 00-1.584-.859 3.004 3.004 0 00-1.747.186 2.948 2.948 0 00-1.178.84L1.5 12l2.755-2.795a4.984 4.984 0 012.105-1.35 4.995 4.995 0 012.91-.187c.929.18 1.78.595 2.43 1.167M6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z">{{ __('All Tools') }}</x-sidebar-link>
            <x-sidebar-link route="marketplace.index" icon="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z">{{ __('Marketplace') }}</x-sidebar-link>
            @foreach ($accessibleModules->take(6) as $module)
                <x-sidebar-link href="{{ url('app/'.$module->route_prefix) }}" active="{{ request()->is('app/'.$module->route_prefix.'*') ? '1' : '0' }}">
                    {{ $module->name }}
                </x-sidebar-link>
            @endforeach
        </div>
    </div>

    <div>
        <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Account') }}</p>
        <div class="mt-1.5 space-y-1">
            <x-sidebar-link route="billing.subscriptions" icon="M9 12.75L11.25 15 15 9.75M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z">{{ __('My Subscriptions') }}</x-sidebar-link>
            <x-sidebar-link route="teams.index" icon="M18 18.72v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48c0-.66.34-1.26.88-1.61l.28-.17a.75.75 0 00.25-1.03l-.11-.18a3.75 3.75 0 00-6.5 0l-.11.18a.75.75 0 00.25 1.03l.28.17c.54.35.88.95.88 1.61v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48a2.25 2.25 0 00-3-2.12 2.25 2.25 0 00-1.5 2.12v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48a6.75 6.75 0 0111.25-5.07 6.75 6.75 0 019.75 5.07zM12 12a3 3 0 100-6 3 3 0 000 6z">{{ __('Companies') }}</x-sidebar-link>
            <x-sidebar-link route="profile" icon="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z">{{ __('Profile') }}</x-sidebar-link>
            @if ($isAdmin)
                <x-sidebar-link route="admin.index" icon="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z">{{ __('Administration') }}</x-sidebar-link>
            @endif
        </div>
    </div>

    <div>
        <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Help') }}</p>
        <div class="mt-1.5 space-y-1">
            <x-sidebar-link route="help.index" icon="M9.879 7.519c1.171-1.026 2.999-1.026 4.17 0 1.172 1.026 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z">{{ __('Help Center') }}</x-sidebar-link>
            <x-sidebar-link route="glossary.index" icon="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.967 8.967 0 00-6 2.292m6-2.292v14.25m0-14.25A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25">{{ __('Knowledge') }}</x-sidebar-link>
        </div>
    </div>
</nav>
