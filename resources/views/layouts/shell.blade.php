<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Allocore Suite') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }">
<div class="min-h-full flex">
    {{-- Sidebar --}}
    <aside class="hidden lg:flex lg:flex-col w-64 shrink-0 bg-slate-900 text-slate-200">
        <div class="flex items-center gap-2 px-6 h-16 border-b border-slate-800">
            <div class="h-8 w-8 rounded-lg bg-indigo-500 flex items-center justify-center font-bold text-white">A</div>
            <span class="text-lg font-semibold text-white">Allocore Suite</span>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                {{ __('Dashboard') }}
            </a>

            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Tools') }}</div>
            @foreach (\App\Models\Module::where('is_active', true)->get() as $module)
                @php($hasAccess = auth()->user()?->hasModule($module->key))
                <a href="{{ $hasAccess ? url('app/'.$module->route_prefix) : route('billing.plans', ['module' => $module->key]) }}"
                   class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium hover:bg-slate-800 {{ $hasAccess ? '' : 'text-slate-400' }}">
                    <span>{{ $module->name }}</span>
                    @unless ($hasAccess)
                        <span class="text-[10px] rounded-full bg-amber-500/20 text-amber-400 px-2 py-0.5">{{ __('Upgrade') }}</span>
                    @endunless
                </a>
            @endforeach

            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Account') }}</div>
            <a href="{{ route('billing.plans') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('billing.plans') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Plans & Pricing') }}</a>
            <a href="{{ route('billing.subscriptions') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('billing.subscriptions') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('My Subscriptions') }}</a>
            <a href="{{ route('teams.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('teams.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Teams') }}</a>

            @if (auth()->user()?->isAdmin())
                <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Admin') }}</div>
                <a href="{{ route('admin.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.index') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    {{ __('Dashboard') }}
                </a>

                <div class="pt-2 pb-1 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('Management') }}</div>
                <a href="{{ route('admin.users.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Users') }}</a>
                <a href="{{ route('admin.teams.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.teams.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Teams') }}</a>

                <div class="pt-2 pb-1 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('Catalog') }}</div>
                <a href="{{ route('admin.modules.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.modules.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Modules') }}</a>
                <a href="{{ route('admin.plans.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.plans.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Plans') }}</a>

                <div class="pt-2 pb-1 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('Billing') }}</div>
                <a href="{{ route('admin.subscriptions.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.subscriptions.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Subscriptions') }}</a>
                <a href="{{ route('admin.invoices.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.invoices.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Invoices') }}</a>
                <a href="{{ route('admin.payments.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.payments.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Payments') }}</a>

                <div class="pt-2 pb-1 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('Tools') }}</div>
                <a href="{{ route('admin.audits.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.audits.index', 'admin.audits.show') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Audits') }}</a>
                <a href="{{ route('admin.audits.templates.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.audits.templates.*', 'admin.audits.pillars.*', 'admin.audits.questions.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Audit Templates') }}</a>
                <a href="{{ route('admin.financial.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.financial.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Financial') }}</a>
                <a href="{{ route('admin.thresholds.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.thresholds.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('KPI Thresholds') }}</a>
                <a href="{{ route('admin.settings.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Site Settings') }}</a>
                <a href="{{ route('admin.mail-settings.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.mail-settings.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Mail Settings') }}</a>
                <a href="{{ route('admin.pages.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.pages.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Pages') }}</a>
            @endif
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        {{-- Topbar --}}
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6">
            <div class="text-sm text-slate-500">
                @if (auth()->user()?->currentTeam)
                    {{ __('Team') }}: <span class="font-medium text-slate-800">{{ auth()->user()->currentTeam->name }}</span>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-700">{{ auth()->user()?->name }}</span>
                <a href="{{ route('profile') }}" class="text-sm text-indigo-600 hover:underline">{{ __('Profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-slate-500 hover:text-slate-800">{{ __('Log out') }}</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if (session('warning'))
                <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">{{ session('warning') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
