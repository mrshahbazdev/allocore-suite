<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100 {{ $theme === 'dark' ? 'dark' : '' }}">
@php($brand = config('app.team_branding') ?? ['name' => config('app.name'), 'logo' => null, 'favicon' => null, 'primary_color' => null, 'accent_color' => null, 'id' => null])
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $brand['primary_color'] ?? '#4f46e5' }}">
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
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }">
<div class="min-h-full flex">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-slate-200 transition-transform duration-200 lg:static lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
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

    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

    <div class="flex-1 flex flex-col min-w-0 lg:ml-0">
        {{-- Topbar --}}
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="{{ __('Toggle menu') }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <div class="text-sm text-slate-500">
                    @if (auth()->user()?->currentTeam)
                        {{ __('Team') }}: <span class="font-medium text-slate-800">{{ auth()->user()->currentTeam->name }}</span>
                    @endif
                </div>
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
                @include('partials.locale-switcher')
                <span class="text-sm font-medium text-slate-700">{{ auth()->user()?->name }}</span>
                <a href="{{ route('profile') }}" class="text-sm text-indigo-600 hover:underline">{{ __('Profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-slate-500 hover:text-slate-800">{{ __('Log out') }}</button>
                </form>
            </div>
        </header>

        @if (request()->is('app/dentaltrack*'))
            @include('dentaltrack::partials.nav')
        @elseif (request()->is('app/orgmatrix*'))
            @include('orgmatrix::partials.nav')
        @elseif (request()->is('app/visionflow*'))
            @include('visionflow::partials.nav')
        @elseif (request()->is('app/nurdu*'))
            @include('nurdu::partials.nav')
        @elseif (request()->is('app/cashcore*'))
            @include('cashcore::partials.nav')
        @elseif (request()->is('app/invoices*'))
            @include('invoicemaker::partials.nav')
        @elseif (request()->is('app/finance*'))
            @include('financialplatform::partials.nav')
        @elseif (request()->is('app/sweetspot*'))
            @include('sweetspot::partials.nav')
        @elseif (request()->is('app/timebutler*'))
            @include('timebutler::partials.nav')
        @elseif (request()->is('app/planhive*'))
            @include('planhive::partials.nav')
        @elseif (request()->is('app/loopengine*'))
            @include('loopengine::partials.nav')
        @endif

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
@include('ai-assistant.floating')
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
    });
</script>
</body>
</html>
