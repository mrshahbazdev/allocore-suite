@php($brand = config('app.team_branding') ?? [])
@php($menu = \App\Models\SiteSetting::value('public_nav_menu', []))
@php
    if (empty($menu)) {
        $menu = [
            ['label' => __('Glossary'), 'url' => route('glossary.index'), 'children' => []],
            ['label' => __('Blog'), 'url' => route('blog.index'), 'children' => []],
            ['label' => __('public.nav.pricing'), 'url' => route('billing.plans'), 'children' => []],
            ['label' => __('API Docs'), 'url' => route('api-docs.index'), 'children' => []],
        ];
    }
@endphp
<header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur" x-data="{ mobileOpen: false }">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8" aria-label="{{ __('Global') }}">
        {{-- Logo --}}
        <a href="/" class="flex items-center gap-3">
            <img src="{{ $brand['logo'] ?? asset('logo-mark.png') }}" alt="" class="h-10 w-10 object-contain rounded-xl bg-white">
            <span class="text-lg font-bold text-slate-900">{{ $brand['name'] ?? config('app.name') }}</span>
        </a>

        {{-- Desktop Navigation with Submenus --}}
        <div class="hidden items-center gap-7 lg:flex">
            @foreach ($menu as $item)
                @if (empty($item['children']))
                    <a href="{{ $item['url'] ?? '#' }}" class="text-sm font-semibold text-slate-700 hover:text-indigo-600 transition">
                        {{ $item['label'] ?? '' }}
                    </a>
                @else
                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative">
                        <button @click="open = !open" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-700 hover:text-indigo-600 transition py-1">
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
                                <a href="{{ $child['url'] ?? '#' }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
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
            <button type="button" @click="mobileOpen = !mobileOpen" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </nav>

    {{-- Mobile Dropdown Menu --}}
    <div x-show="mobileOpen" x-cloak class="border-t border-slate-200 bg-white px-6 py-4 lg:hidden">
        <div class="space-y-2">
            @foreach ($menu as $item)
                @if (empty($item['children']))
                    <a href="{{ $item['url'] ?? '#' }}" class="block rounded-lg px-3 py-2 text-base font-semibold text-slate-800 hover:bg-slate-100">
                        {{ $item['label'] ?? '' }}
                    </a>
                @else
                    <div x-data="{ subOpen: false }" class="space-y-1">
                        <button @click="subOpen = !subOpen" type="button" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-base font-semibold text-slate-800 hover:bg-slate-100">
                            <span>{{ $item['label'] ?? '' }}</span>
                            <svg class="h-5 w-5 transition-transform duration-200" :class="subOpen ? 'rotate-180 text-indigo-600' : 'text-slate-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="subOpen" x-cloak class="pl-4 space-y-1 border-l-2 border-indigo-200 ml-3">
                            @foreach ($item['children'] as $child)
                                <a href="{{ $child['url'] ?? '#' }}" class="block rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-indigo-50 hover:text-indigo-600">
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
                <a href="{{ route('dashboard') }}" class="w-full rounded-lg px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm" style="background-color: {{ $brand['primary_color'] ?? '#ff9200' }}">
                    {{ __('Dashboard') }}
                </a>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('landing.nav.login') }}</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="w-full rounded-lg px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm" style="background-color: {{ $brand['primary_color'] ?? '#ff9200' }}">{{ __('public.nav.get_started') }}</a>
                @endif
            @endauth
        </div>
    </div>
</header>
