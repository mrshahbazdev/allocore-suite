<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100 {{ $theme === 'dark' ? 'dark' : '' }}">
@php($brand = config('app.team_branding') ?? ['name' => config('app.name'), 'logo' => null, 'favicon' => null, 'primary_color' => null, 'accent_color' => null, 'id' => null])
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $brand['primary_color'] ?? '#4f46e5' }}">
    @if ($brand['favicon'])
        <link rel="icon" href="{{ $brand['favicon'] }}">
    @endif
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <title>{{ $title ?? $brand['name'] }}</title>
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
            @if ($brand['logo'])
                <img src="{{ $brand['logo'] }}" alt="" class="h-8 w-8 object-contain">
            @else
                <div class="h-8 w-8 rounded-lg bg-indigo-500 flex items-center justify-center font-bold text-white">A</div>
            @endif
            <span class="text-lg font-semibold text-white">{{ $brand['name'] ?? 'Allocore Suite' }}</span>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                {{ __('Dashboard') }}
            </a>

            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Tools') }}</div>
            <a href="{{ route('tools.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('tools.index') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                {{ __('All Tools') }}
            </a>
            <a href="{{ route('workspace.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('workspace.index') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM2.25 13.125c0-.621.504-1.125 1.125-1.125h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 7.125c0-.621.504-1.125 1.125-1.125h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 13.125c0-.621.504-1.125 1.125-1.125h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75Z" /></svg>
                {{ __('Workspace') }}
            </a>
            @php($accessibleModules = \App\Models\Module::where('is_active', true)->get()->filter(fn ($module) => auth()->user()?->hasModule($module->key)))
            @foreach ($accessibleModules as $module)
                <a href="{{ url('app/'.$module->route_prefix) }}"
                   class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium hover:bg-slate-800">
                    <span>{{ $module->name }}</span>
                </a>
            @endforeach

            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Insights') }}</div>
            <a href="{{ route('advisor.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('advisor.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('AI Advisor') }}</a>
            <a href="{{ route('usage.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('usage.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Usage Analytics') }}</a>
            <a href="{{ route('timeline.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('timeline.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Activity Timeline') }}</a>
            <a href="{{ route('search.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('search.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Search') }}</a>
            <a href="{{ route('alerts.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('alerts.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Alerts') }}</a>
            <a href="{{ route('workflows.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('workflows.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Workflows') }}</a>
            <a href="{{ route('recommendations.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('recommendations.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Recommendations') }}</a>

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
                <a href="{{ route('admin.blog.posts.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.blog.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Blog') }}</a>

                <div class="pt-2 pb-1 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('System') }}</div>
                <a href="{{ route('admin.status-incidents.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.status-incidents.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Status Incidents') }}</a>
                <a href="{{ route('admin.analytics.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Analytics') }}</a>
                <a href="{{ route('admin.activity-logs.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.activity-logs.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Activity Logs') }}</a>
                <a href="{{ route('admin.roles.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Roles') }}</a>
                <a href="{{ route('admin.support-tickets.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.support-tickets.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Support Tickets') }}</a>
                <a href="{{ route('admin.integrations.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.integrations.*', 'admin.webhooks.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Integrations') }}</a>

                <div class="pt-2 pb-1 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-600">{{ __('Module Data') }}</div>
                <a href="{{ route('admin.module-data.index', ['invoice-maker', 'clients']) }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.module-data.index') && request()->route('group') === 'invoice-maker' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('InvoiceMaker') }}</a>
                <a href="{{ route('admin.module-data.index', ['lead-os', 'contacts']) }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.module-data.index') && request()->route('group') === 'lead-os' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('LeadOS') }}</a>
                <a href="{{ route('admin.module-data.index', ['financial', 'companies']) }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.module-data.index') && request()->route('group') === 'financial' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Financial') }}</a>
                <a href="{{ route('admin.module-data.index', ['audit-pro', 'audits']) }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.module-data.index') && request()->route('group') === 'audit-pro' ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('AuditPro') }}</a>
                <a href="{{ route('admin.announcements.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.announcements.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Announcements') }}</a>
                <a href="{{ route('admin.media.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.media.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Media') }}</a>
                <a href="{{ route('admin.coupons.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.coupons.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Coupons') }}</a>
                <a href="{{ route('admin.tax-rates.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.tax-rates.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Tax Rates') }}</a>
                <a href="{{ route('admin.notification-templates.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.notification-templates.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('admin.notification_templates.title') }}</a>
                <a href="{{ route('admin.queue-monitor.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.queue-monitor.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Queue Monitor') }}</a>
                <a href="{{ route('admin.api-tokens.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.api-tokens.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('API Tokens') }}</a>
                <a href="{{ route('admin.notifications.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.notifications.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Notifications') }}</a>
                <a href="{{ route('admin.log-viewer.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.log-viewer.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Logs') }}</a>
                <a href="{{ route('admin.session-manager.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.session-manager.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Sessions') }}</a>
                <a href="{{ route('admin.backups.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.backups.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Backups') }}</a>
                <a href="{{ route('admin.exports.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.exports.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Exports') }}</a>
                <a href="{{ route('admin.maintenance.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.maintenance.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">{{ __('Maintenance') }}</a>
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
                @if (session('impersonated_by'))
                    <a href="{{ route('impersonation.stop') }}" class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-200">{{ __('admin.impersonation.stop') }}</a>
                @endif
                @auth
                    <a href="{{ route('notifications.index') }}" class="relative text-slate-500 hover:text-slate-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.454 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                        @php($unreadCount = auth()->user()->unreadNotifications()->count())
                        @if ($unreadCount > 0)
                            <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </a>
                @endauth
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
@include('partials.cookie-consent')
@livewireScripts
@stack('scripts')
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
</script>
</body>
</html>
