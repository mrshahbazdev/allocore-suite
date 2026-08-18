<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100 {{ $theme === 'dark' ? 'dark' : '' }}">
@php($brand = config('app.team_branding') ?? ['name' => config('app.name'), 'logo' => null, 'favicon' => null, 'primary_color' => null, 'accent_color' => null, 'id' => null])
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $brand['primary_color'] ?? '#ff9200' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
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
    @stack('styles')
    <style>[x-cloak] { display: none !important; }</style>
</head>
@php($isModulePage = request()->is('app/*'))
@php($currentModule = $isModulePage ? \App\Models\Module::where('route_prefix', request()->segment(2))->where('is_active', true)->first() : null)
@php($pageTitle = $currentModule?->name ?? ($isModulePage ? __('Tools') : __('Dashboard')))
<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">
<div id="nav-progress" class="fixed left-0 top-0 z-[60] h-1 w-0 bg-[#ff9200] shadow-[0_0_8px_rgba(255,146,0,0.7)] transition-[width] duration-300 ease-out" aria-hidden="true"></div>
<div class="min-h-full flex">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 transform overflow-hidden bg-slate-900 text-slate-200 transition-all duration-300 ease-in-out md:static md:translate-x-0"
           :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'md:w-0' : 'md:w-64']"
           x-cloak>
        <div class="flex items-center gap-2 px-6 h-16 border-b border-slate-800">
            @if ($brand['logo'])
                <img src="{{ $brand['logo'] }}" alt="" class="h-8 w-8 object-contain">
            @else
                <div class="h-8 w-8 rounded-lg bg-indigo-500 flex items-center justify-center font-bold text-white">A</div>
            @endif
            <span class="text-lg font-semibold text-white">{{ $brand['name'] ?? 'Allocore Suite' }}</span>
        </div>

        @include('partials.sidebar')
    </aside>

    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/50 md:hidden"></div>

    <div class="flex-1 flex flex-col min-w-0 lg:ml-0">
        {{-- Topbar --}}
        <header class="bg-white border-b border-slate-200">
            <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                {{-- Left: brand + page title --}}
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:hidden" aria-label="{{ __('Toggle menu') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:flex" aria-label="{{ __('Toggle sidebar') }}">
                        <svg class="h-5 w-5 transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#ff9200] text-sm font-bold text-white">A</div>
                        <span class="hidden text-lg font-semibold text-slate-900 sm:inline">{{ $brand['name'] ?? 'Allocore' }}</span>
                    </a>
                    <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
                    <span class="truncate text-base font-semibold text-slate-700">{{ $pageTitle }}</span>
                </div>

                {{-- Right: page actions, notifications, language, user --}}
                <div class="flex items-center gap-2 sm:gap-4">
                    @yield('topbar-actions')

                    @if (session('impersonated_by'))
                        <a href="{{ route('impersonation.stop') }}" class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-200">{{ __('admin.impersonation.stop') }}</a>
                    @endif
                    @auth
                        <a href="{{ route('notifications.index') }}" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.454 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                            @php($unreadCount = auth()->user()->unreadNotifications()->count())
                            @if ($unreadCount > 0)
                                <span class="absolute right-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </a>
                    @endauth
                    @include('partials.locale-switcher')
                    @include('partials.team-switcher')

                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-slate-100">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0094af] text-xs font-bold text-white">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="hidden text-sm font-medium text-slate-700 sm:inline">{{ auth()->user()->name }}</span>
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 z-50 mt-2 w-48 rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">{{ __('Profile') }}</a>
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">{{ __('Dashboard') }}</a>
                                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">
                                    @csrf
                                    <button class="block w-full px-4 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">{{ __('Log out') }}</button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        @include('partials.module-header')

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
@auth
    @if (Auth::user()->isAdmin() || Auth::user()->isOwner() || Auth::user()->hasAnyRole(['employee', 'saas-developer', 'senior-management', 'quality']))
        @include('ai-assistant.floating')
    @endif
@endauth
@livewireScripts
@stack('scripts')
<script>
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    (function () {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.setAttribute('style', 'position:fixed;top:1rem;right:1rem;z-index:50;display:flex;flex-direction:column;gap:0.5rem;');
        document.body.appendChild(container);

        const showToast = (title, body, url) => {
            const toast = document.createElement('div');
            toast.className = 'max-w-xs rounded-lg border border-slate-200 bg-white p-4 shadow-lg';
            toast.innerHTML = '<div class="font-semibold text-slate-900">' + title + '</div>' +
                '<div class="mt-1 text-sm text-slate-600">' + body + '</div>';
            if (url) {
                toast.classList.add('cursor-pointer');
                toast.addEventListener('click', () => window.location.href = url);
            }
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 6000);
        };

        if (typeof EventSource !== 'undefined') {
            const source = new EventSource('{{ route('notifications.stream') }}');

            source.addEventListener('notification', (event) => {
                try {
                    const data = JSON.parse(event.data);
                    showToast(data.title, data.body, data.url);

                    if ('Notification' in window && Notification.permission === 'granted') {
                        const notification = new Notification(data.title, {
                            body: data.body,
                            icon: '/icon-192.png',
                        });
                        if (data.url) {
                            notification.onclick = () => window.location.href = data.url;
                        }
                    }
                } catch (e) {}
            });

            source.addEventListener('error', () => {
                setTimeout(() => {
                    if (source.readyState === EventSource.CLOSED) {
                        source.close();
                    }
                }, 5000);
            });
        }
    })();

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }

    // Wrap tables that are not already inside a scroll container so they are mobile-friendly
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('main table').forEach((table) => {
            if (table.parentElement && table.parentElement.classList.contains('overflow-x-auto')) {
                return;
            }
            const wrapper = document.createElement('div');
            wrapper.className = 'overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });

        // Top navigation progress bar for Livewire/Alpine navigations
        const navProgress = document.getElementById('nav-progress');
        if (navProgress) {
            document.addEventListener('livewire:navigating', () => {
                navProgress.classList.remove('w-0');
                navProgress.classList.add('w-3/4');
            });
            document.addEventListener('livewire:navigated', () => {
                navProgress.classList.remove('w-3/4');
                navProgress.classList.add('w-full');
                setTimeout(() => {
                    navProgress.classList.remove('w-full');
                    navProgress.classList.add('w-0');
                }, 150);
            });
        }

        // SPA-style navigation for internal links and GET forms without page refresh
        if (window.Alpine && window.Alpine.navigate) {
            document.addEventListener('click', (e) => {
                const a = e.target.closest('a');
                if (! a || e.defaultPrevented) return;
                const href = a.getAttribute('href');
                if (! href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
                if (a.target === '_blank' || a.hasAttribute('download') || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
                const url = new URL(href, window.location.href);
                if (url.hostname !== window.location.hostname) return;
                e.preventDefault();
                window.Alpine.navigate(url.pathname + url.search + url.hash);
            });

            document.addEventListener('submit', (e) => {
                if (e.defaultPrevented) return;
                const form = e.target;
                if (form.method?.toLowerCase() !== 'get' || form.dataset.noNavigate !== undefined) return;
                const action = form.getAttribute('action') || window.location.pathname;
                const actionUrl = new URL(action, window.location.href);
                if (actionUrl.hostname !== window.location.hostname) return;
                e.preventDefault();
                const params = new URLSearchParams(new FormData(form));
                const url = actionUrl.pathname + (params.toString() ? '?' + params.toString() : '') + actionUrl.hash;
                window.Alpine.navigate(url);
            });
        }
    });
</script>
</body>
</html>
