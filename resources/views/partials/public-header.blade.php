@php
    $brand = config('app.team_branding') ?? [];
    $menu = \App\Models\SiteSetting::value('public_nav_menu', []);
    if (empty($menu)) {
        $menu = [
            ['label' => __('Glossary'), 'url' => route('glossary.index'), 'children' => []],
            ['label' => __('Blog'), 'url' => route('blog.index'), 'children' => []],
            ['label' => __('public.nav.pricing'), 'url' => route('billing.plans'), 'children' => []],
            ['label' => __('API Docs'), 'url' => route('api-docs.index'), 'children' => []],
        ];
    }
    $menuGap = (int) \App\Models\SiteSetting::value('menu_gap', 28);
    $menuPaddingX = (int) \App\Models\SiteSetting::value('menu_padding_x', 14);
    $menuPaddingY = (int) \App\Models\SiteSetting::value('menu_padding_y', 8);
    $menuFontSize = (int) \App\Models\SiteSetting::value('menu_font_size', 15);
    $menuFontWeight = \App\Models\SiteSetting::value('menu_font_weight', '600');
    $menuLinkColor = \App\Models\SiteSetting::value('menu_link_color', '#334155');
    $menuHoverColor = \App\Models\SiteSetting::value('menu_hover_color', '#4f46e5');
@endphp

<style>
    :root {
        --menu-gap: {{ $menuGap }}px;
        --menu-pad-x: {{ $menuPaddingX }}px;
        --menu-pad-y: {{ $menuPaddingY }}px;
        --menu-font-size: {{ $menuFontSize }}px;
        --menu-font-weight: {{ $menuFontWeight }};
        --menu-link-color: {{ $menuLinkColor }};
        --menu-hover-color: {{ $menuHoverColor }};
    }
    @media (min-width: 1024px) {
        .site-nav-container {
            display: flex !important;
            align-items: center !important;
            gap: var(--menu-gap, 28px) !important;
        }
    }
    @media (max-width: 1023.98px) {
        .site-nav-container {
            display: none !important;
        }
    }
    .site-nav-item {
        display: inline-flex !important;
        align-items: center !important;
        padding: var(--menu-pad-y, 8px) var(--menu-pad-x, 14px) !important;
        border-radius: 0.5rem !important;
        font-size: var(--menu-font-size, 15px) !important;
        font-weight: var(--menu-font-weight, 600) !important;
        color: var(--menu-link-color, #334155) !important;
        transition: all 0.15s ease-in-out !important;
        text-decoration: none !important;
        white-space: nowrap !important;
    }
    .site-nav-item:hover {
        color: var(--menu-hover-color, #4f46e5) !important;
        background-color: #f1f5f9 !important;
    }
    .site-nav-dropdown-item {
        display: flex !important;
        align-items: center !important;
        padding: 0.5rem 0.75rem !important;
        border-radius: 0.5rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: var(--menu-link-color, #334155) !important;
        transition: all 0.15s ease-in-out !important;
        text-decoration: none !important;
    }
    .site-nav-dropdown-item:hover {
        color: var(--menu-hover-color, #4f46e5) !important;
        background-color: #eef2ff !important;
    }
</style>

<header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur" x-data="{ mobileOpen: false }">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8" aria-label="{{ __('Global') }}">
        {{-- Logo --}}
        <a href="/" class="flex items-center gap-3">
            <img src="{{ $brand['logo'] ?? asset('logo-mark.png') }}" alt="" class="h-10 w-10 object-contain rounded-xl bg-white">
            <span class="text-lg font-bold text-slate-900">{{ $brand['name'] ?? config('app.name') }}</span>
        </a>

        {{-- Desktop Navigation with Submenus --}}
        <div class="site-nav-container hidden items-center lg:flex">
            @foreach ($menu as $item)
                @if (empty($item['children']))
                    <a href="{{ $item['url'] ?? '#' }}" class="site-nav-item">
                        {{ $item['label'] ?? '' }}
                    </a>
                @else
                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative">
                        <button @click="open = !open" type="button" class="site-nav-item inline-flex items-center gap-1.5">
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
                             class="absolute left-0 top-full z-50 mt-1.5 w-60 rounded-xl border border-slate-200 bg-white p-2 shadow-xl ring-1 ring-black/5">
                            @foreach ($item['children'] as $child)
                                <a href="{{ $child['url'] ?? '#' }}" class="site-nav-dropdown-item">
                                    <span>{{ $child['label'] ?? '' }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Desktop Right Actions --}}
        <div class="hidden items-center gap-4 lg:flex">
            @include('partials.locale-switcher')

            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90 transition" style="background-color: {{ $brand['primary_color'] ?? '#ff9200' }}">
                    {{ __('Dashboard') }}
                </a>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-indigo-600 transition">{{ __('landing.nav.login') }}</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-white hover:opacity-90 shadow-sm transition" style="background-color: {{ $brand['primary_color'] ?? '#ff9200' }}">{{ __('public.nav.get_started') }}</a>
                @endif
            @endauth
        </div>

        {{-- Mobile Hamburger & Switcher --}}
        <div class="flex items-center gap-2 lg:hidden">
            @include('partials.locale-switcher')
            <button type="button" @click="mobileOpen = !mobileOpen" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 transition" aria-label="{{ __('Toggle navigation menu') }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </nav>

    {{-- Mobile Dropdown Menu --}}
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="border-t border-slate-200 bg-white px-6 py-4 lg:hidden shadow-lg">
        <div class="space-y-1">
            @foreach ($menu as $item)
                @if (empty($item['children']))
                    <a href="{{ $item['url'] ?? '#' }}" class="block rounded-lg px-3 py-2.5 text-base font-semibold text-slate-800 hover:bg-slate-100 transition" style="color: {{ $menuLinkColor }};">
                        {{ $item['label'] ?? '' }}
                    </a>
                @else
                    <div x-data="{ subOpen: false }" class="space-y-1">
                        <button @click="subOpen = !subOpen" type="button" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-base font-semibold text-slate-800 hover:bg-slate-100 transition" style="color: {{ $menuLinkColor }};">
                            <span>{{ $item['label'] ?? '' }}</span>
                            <svg class="h-5 w-5 transition-transform duration-200" :class="subOpen ? 'rotate-180 text-indigo-600' : 'text-slate-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="subOpen" x-cloak class="pl-4 space-y-1 border-l-2 border-indigo-200 ml-3 py-1">
                            @foreach ($item['children'] as $child)
                                <a href="{{ $child['url'] ?? '#' }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                    {{ $child['label'] ?? '' }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-4 pt-4 border-t border-slate-200 flex flex-col gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="w-full rounded-lg px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:opacity-95 transition" style="background-color: {{ $brand['primary_color'] ?? '#ff9200' }}">
                    {{ __('Dashboard') }}
                </a>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">{{ __('landing.nav.login') }}</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="w-full rounded-lg px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:opacity-95 transition" style="background-color: {{ $brand['primary_color'] ?? '#ff9200' }}">{{ __('public.nav.get_started') }}</a>
                @endif
            @endauth
        </div>
    </div>
</header>
