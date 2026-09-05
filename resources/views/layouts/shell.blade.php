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
    @else
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @endif
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <title>{{ $title ?? $brand['name'] }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
        .glossary-link {
            display: inline !important;
            font-weight: 600 !important;
            color: #334155 !important;
            text-decoration: underline dashed 1.5px !important;
            text-decoration-color: #64748b !important;
            text-underline-offset: 4px !important;
            cursor: pointer !important;
            padding: 1px 3px !important;
            border-radius: 4px !important;
            background-color: rgba(100, 116, 139, 0.08) !important;
            transition: all 0.2s ease-in-out !important;
        }
        .glossary-link:hover {
            color: #0f172a !important;
            background-color: rgba(100, 116, 139, 0.2) !important;
            text-decoration-style: solid !important;
            text-decoration-color: #0f172a !important;
        }

        /* Inside ANY white card or container on any section */
        .bg-white .glossary-link,
        .card .glossary-link,
        [class*="bg-white"] .glossary-link {
            color: #334155 !important;
            text-decoration-color: #64748b !important;
            background-color: rgba(100, 116, 139, 0.08) !important;
        }
        .bg-white .glossary-link:hover,
        .card .glossary-link:hover,
        [class*="bg-white"] .glossary-link:hover {
            color: #0f172a !important;
            background-color: rgba(100, 116, 139, 0.2) !important;
            text-decoration-color: #0f172a !important;
        }

        /* On colored / teal / dark sections (Banner Text Blocks) */
        .is-dark-section .glossary-link,
        section[style*="background-color"] .prose .glossary-link,
        section[style*="background-color"] > div > p .glossary-link,
        .prose[style*="color: rgb(255"] .glossary-link,
        .prose[style*="color: #fff"] .glossary-link,
        .prose[style*="color:#fff"] .glossary-link,
        .dark:not(.bg-white) .glossary-link {
            color: #e2e8f0 !important; /* light silver-grey */
            text-decoration: underline dashed 1.5px !important;
            text-decoration-color: #cbd5e1 !important; /* soft light grey dashed underline */
            background-color: rgba(255, 255, 255, 0.12) !important;
        }
        .is-dark-section .glossary-link:hover,
        section[style*="background-color"] .prose .glossary-link:hover,
        section[style*="background-color"] > div > p .glossary-link:hover,
        .dark:not(.bg-white) .glossary-link:hover {
            color: #ffffff !important;
            text-decoration-color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.25) !important;
        }
    </style>
</head>
@php
    $isModulePage = request()->is('app/*');
    $currentModule = $isModulePage ? \App\Models\Module::where('route_prefix', request()->segment(2))->where('is_active', true)->first() : null;
    $pageTitle = $pageTitle ?? ($currentModule?->name ?? ($isModulePage ? __('Tools') : __('Dashboard')));
@endphp
<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">
<div id="nav-progress" class="fixed left-0 top-0 z-[60] h-1 w-0 bg-[#ff9200] shadow-[0_0_8px_rgba(255,146,0,0.7)] transition-[width] duration-300 ease-out" aria-hidden="true"></div>
<div class="min-h-full flex">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 transform overflow-hidden bg-slate-900 text-slate-200 transition-all duration-300 ease-in-out md:static md:translate-x-0"
           :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'md:w-0' : 'md:w-64']"
           x-cloak>
        <div class="flex items-center gap-2 px-6 h-16 border-b border-slate-800">
            <img src="{{ $brand['logo'] ?? asset('logo-mark.png') }}" alt="" class="h-8 w-8 object-contain rounded-lg bg-white">
            <span class="text-lg font-semibold text-white">{{ $brand['name'] ?? 'Allocore Suite' }}</span>
        </div>

        @include('partials.sidebar')
    </aside>

    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/50 md:hidden"></div>

    <div class="flex-1 flex flex-col min-w-0 lg:ml-0">
        {{-- Topbar --}}
        <header class="bg-white border-b border-slate-200">
            <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                {{-- Left: page title --}}
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:hidden" aria-label="{{ __('Toggle menu') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"></path></svg>
                    </button>
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:flex" aria-label="{{ __('Toggle sidebar') }}">
                        <svg class="h-5 w-5 transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"></path></svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex h-8 w-8 shrink-0 items-center justify-center" aria-label="{{ $brand['name'] ?? 'Allocore' }}">
                        <img src="{{ asset('logo-mark.png') }}" class="h-8 w-8 object-contain" alt="{{ $brand['name'] ?? 'Allocore' }}">
                    </a>
                    <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
                    <span class="truncate text-base font-semibold text-slate-700">{{ $pageTitle }}</span>
                </div>

                {{-- Center: Global Custom Navigation Menu with Submenus (Always Visible) --}}
                @php($shellMenu = \App\Models\SiteSetting::value('public_nav_menu', []))
                @if (!empty($shellMenu))
                    <nav class="hidden lg:flex items-center gap-6 shrink-0 mx-2">
                        @foreach ($shellMenu as $item)
                            @if (empty($item['children']))
                                <a href="{{ $item['url'] ?? '#' }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition">
                                    {{ $item['label'] ?? '' }}
                                </a>
                            @else
                                <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative">
                                    <button @click="open = !open" class="inline-flex items-center gap-1 text-sm font-medium text-slate-600 hover:text-slate-900 transition py-1">
                                        <span>{{ $item['label'] ?? '' }}</span>
                                        <svg class="h-4 w-4 transition-transform duration-200 text-slate-400" :class="open ? 'rotate-180 text-indigo-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-cloak
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                         class="absolute left-0 top-full z-50 mt-1 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl ring-1 ring-black/5">
                                        @foreach ($item['children'] as $child)
                                            <a href="{{ $child['url'] ?? '#' }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                                {{ $child['label'] ?? '' }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </nav>
                @endif

                {{-- Right: page actions, notifications, user --}}
                <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                    @yield('topbar-actions')

                    @if (session('impersonated_by'))
                        <a href="{{ route('impersonation.stop') }}" class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-200">{{ __('admin.impersonation.stop') }}</a>
                    @endif
                    @auth
                        <a href="{{ route('notifications.index') }}" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700" aria-label="{{ __('Notifications') }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 002.688 6.062M9 15.25c.966 0 1.875-.1 2.75-.3m0 0a24.15 24.15 0 015.5.3m0 0a23.961 23.961 0 01-5.5-.3m0 0c.966.293 1.875.7 2.75 1.194M9 15.25c1.034 0 2.052-.115 3-3m5.25 0a23.95 23.95 0 01-3 3m0 0a23.95 23.95 0 01-5.25 0m0 0v-3m0 3v-6m0 0c-1.034 0-2.052.115-3 .3m7.5 0a23.96 23.96 0 003-.3"></path></svg>
                            @php($unreadCount = auth()->user()->unreadNotifications()->count())
                            @if ($unreadCount > 0)
                                <span class="absolute right-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </a>
                        @include('partials.user-menu')
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
