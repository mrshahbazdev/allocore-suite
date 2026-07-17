<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth bg-white">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ \App\Models\SiteSetting::value('site_name', config('app.name', 'Allocore Suite')) }}</title>
        <meta name="description" content="{{ \App\Models\SiteSetting::value('hero_subheading', __('landing.hero.subheading')) }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans text-slate-600 antialiased">
        @php($modules = \App\Models\Module::where('is_active', true)->get())
        @php($siteName = \App\Models\SiteSetting::value('site_name', config('app.name', 'Allocore Suite')))

        <div class="flex min-h-full flex-col bg-slate-50">
            {{-- Header --}}
            <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
                <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8" aria-label="Global">
                    <a href="/" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-lg font-black text-white">A</div>
                        <span class="text-lg font-bold text-slate-900">{{ $siteName }}</span>
                    </a>

                    <div class="hidden items-center gap-8 lg:flex">
                        <a href="#features" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('landing.nav.features') }}</a>
                        <a href="#modules" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('landing.nav.modules') }}</a>
                        <a href="{{ route('billing.plans') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('landing.nav.pricing') }}</a>
                    </div>

                    <div class="hidden items-center gap-4 lg:flex">
                        @include('partials.locale-switcher')

                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('landing.nav.dashboard') }}</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('landing.nav.login') }}</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('landing.nav.get_started') }}</a>
                            @endif
                        @endauth
                    </div>

                    <button type="button" id="mobile-menu-button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-slate-600 lg:hidden" aria-expanded="false" aria-controls="mobile-menu">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </nav>

                {{-- Mobile menu --}}
                <div id="mobile-menu" class="hidden border-t border-slate-200 bg-white lg:hidden">
                    <div class="mx-auto max-w-7xl px-6 py-4">
                        <div class="flex flex-col gap-2">
                            <a href="#features" class="mobile-menu-link rounded-lg px-4 py-3 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">{{ __('landing.nav.features') }}</a>
                            <a href="#modules" class="mobile-menu-link rounded-lg px-4 py-3 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">{{ __('landing.nav.modules') }}</a>
                            <a href="{{ route('billing.plans') }}" class="rounded-lg px-4 py-3 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">{{ __('landing.nav.pricing') }}</a>
                            <div class="mt-2 border-t border-slate-200 pt-4">
                                @include('partials.locale-switcher')
                            </div>
                            <div class="mt-2 flex flex-col gap-2 border-t border-slate-200 pt-4">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-indigo-700">{{ __('landing.nav.dashboard') }}</a>
                                @else
                                    <a href="{{ route('login') }}" class="rounded-lg px-4 py-3 text-center text-sm font-medium text-slate-700 hover:bg-slate-100">{{ __('landing.nav.login') }}</a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-indigo-700">{{ __('landing.nav.get_started') }}</a>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Hero --}}
            <main class="flex-1">
                <section class="border-b border-slate-200 bg-white pb-24 pt-16 lg:pt-24">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="mx-auto max-w-3xl text-center">
                            <div class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-4 py-1.5 text-sm font-medium text-indigo-700">
                                <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                                {{ __('landing.hero.badge') }}
                            </div>
                            <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl">
                                {{ \App\Models\SiteSetting::value('hero_heading', __('landing.hero.heading')) }}
                            </h1>
                            <p class="mt-6 text-lg leading-8 text-slate-600 sm:text-xl">
                                {{ \App\Models\SiteSetting::value('hero_subheading', __('landing.hero.subheading')) }}
                            </p>
                            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-7 py-3.5 text-base font-semibold text-white hover:bg-indigo-700">{{ __('landing.hero.open_dashboard') }}</a>
                                @else
                                    @php($primary = \App\Models\SiteSetting::value('hero_cta_primary_link', route('register')))
                                    @php($secondary = \App\Models\SiteSetting::value('hero_cta_secondary_link', route('login')))
                                    <a href="{{ $primary }}" class="rounded-lg bg-indigo-600 px-7 py-3.5 text-base font-semibold text-white hover:bg-indigo-700">{{ \App\Models\SiteSetting::value('hero_cta_primary_label', __('landing.hero.cta_primary')) }}</a>
                                    <a href="{{ $secondary }}" class="rounded-lg border border-slate-300 bg-white px-7 py-3.5 text-base font-semibold text-slate-700 hover:bg-slate-50">{{ \App\Models\SiteSetting::value('hero_cta_secondary_label', __('landing.hero.cta_secondary')) }}</a>
                                @endauth
                            </div>
                        </div>

                        <div class="mx-auto mt-16 grid max-w-4xl gap-6 sm:grid-cols-3">
                            @foreach ([
                                ['label' => __('landing.stats.central_auth'), 'value' => __('landing.stats.value_one_login')],
                                ['label' => __('landing.stats.teams_billing'), 'value' => __('landing.stats.value_shared')],
                                ['label' => __('landing.stats.per_user'), 'value' => __('landing.stats.value_module_gated')],
                            ] as $stat)
                                <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</div>
                                    <div class="mt-2 text-lg font-bold text-slate-900">{{ $stat['value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Features --}}
                <section id="features" class="bg-slate-50 py-24">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ __('landing.features.heading') }}</h2>
                            <p class="mt-4 text-lg text-slate-600">{{ __('landing.features.subheading') }}</p>
                        </div>

                        <div class="mx-auto mt-16 grid max-w-5xl gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ([
                                ['icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.637-2.911M15 19.128V13.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 13.5v3.75m-3-1.837a6.375 6.375 0 0111.637-2.911c1.249 1.037 2.058 2.451 2.305 3.97', 'title' => 'auth', 'desc' => 'auth'],
                                ['icon' => 'M18 18.72v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48c0-.66.34-1.26.88-1.61l.28-.17a.75.75 0 00.25-1.03l-.11-.18a3.75 3.75 0 00-6.5 0l-.11.18a.75.75 0 00.25 1.03l.28.17c.54.35.88.95.88 1.61v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48a2.25 2.25 0 00-3-2.12 2.25 2.25 0 00-1.5 2.12v.48a.75.75 0 01-.75.75H3a.75.75 0 01-.75-.75v-.48a6.75 6.75 0 0111.25-5.07 6.75 6.75 0 019.75 5.07zM12 12a3 3 0 100-6 3 3 0 000 6z', 'title' => 'teams', 'desc' => 'teams'],
                                ['icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z', 'title' => 'billing', 'desc' => 'billing'],
                                ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', 'title' => 'analytics', 'desc' => 'analytics'],
                            ] as $feature)
                                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/></svg>
                                    </div>
                                    <h3 class="mt-4 text-lg font-semibold text-slate-900">
                                        {{ \App\Models\SiteSetting::value('feature_'.$feature['title'].'_title', __('landing.features.'.$feature['title'].'.title')) }}
                                    </h3>
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                                        {{ \App\Models\SiteSetting::value('feature_'.$feature['title'].'_desc', __('landing.features.'.$feature['title'].'.desc')) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Modules --}}
                <section id="modules" class="bg-white py-24">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ __('landing.modules.heading') }}</h2>
                            <p class="mt-4 text-lg text-slate-600">{{ __('landing.modules.subheading') }}</p>
                        </div>

                        <div class="mx-auto mt-16 grid max-w-5xl gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @forelse ($modules as $module)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 transition hover:border-indigo-200">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-900">{{ $module->name }}</h3>
                                            <p class="mt-2 text-sm text-slate-600">{{ $module->description }}</p>
                                        </div>
                                        <span class="inline-flex shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">{{ __('landing.modules.ready') }}</span>
                                    </div>
                                    <div class="mt-6 flex items-center gap-2 text-sm font-medium text-indigo-600">
                                        <span>{{ $module->route_prefix }}</span>
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center text-slate-500">
                                    {{ __('landing.modules.empty') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                {{-- CTA --}}
                <section class="bg-slate-900 py-20">
                    <div class="mx-auto max-w-4xl px-6 text-center lg:px-8">
                        <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ __('landing.cta.heading') }}</h2>
                        <p class="mt-4 text-lg text-slate-300">{{ __('landing.cta.subheading') }}</p>
                        <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            @auth
                                <a href="{{ route('dashboard') }}" class="rounded-lg bg-white px-7 py-3.5 text-base font-semibold text-slate-900 hover:bg-slate-100">{{ __('landing.nav.dashboard') }}</a>
                            @else
                                @php($primary = \App\Models\SiteSetting::value('cta_primary_link', route('register')))
                                @php($secondary = \App\Models\SiteSetting::value('cta_secondary_link', route('billing.plans')))
                                <a href="{{ $primary }}" class="rounded-lg bg-white px-7 py-3.5 text-base font-semibold text-slate-900 hover:bg-slate-100">{{ \App\Models\SiteSetting::value('cta_primary_label', __('landing.cta.primary')) }}</a>
                                <a href="{{ $secondary }}" class="rounded-lg border border-slate-600 bg-transparent px-7 py-3.5 text-base font-semibold text-white hover:bg-slate-800">{{ \App\Models\SiteSetting::value('cta_secondary_label', __('landing.cta.secondary')) }}</a>
                            @endauth
                        </div>
                    </div>
                </section>
            </main>

            {{-- Footer --}}
            <footer class="border-t border-slate-200 bg-white py-12">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-base font-black text-white">A</div>
                            <span class="text-sm font-semibold text-slate-900">{{ $siteName }}</span>
                        </div>
                        <div class="flex items-center gap-6 text-sm text-slate-600">
                            <a href="{{ route('login') }}" class="hover:text-slate-900">{{ __('landing.nav.login') }}</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="hover:text-slate-900">{{ __('landing.nav.get_started') }}</a>
                            @endif
                            <a href="{{ route('billing.plans') }}" class="hover:text-slate-900">{{ __('landing.nav.pricing') }}</a>
                        </div>
                        <p class="text-xs text-slate-500">&copy; {{ date('Y') }} {{ $siteName }}. {{ \App\Models\SiteSetting::value('footer_text', __('landing.footer.copyright')) }}</p>
                    </div>
                </div>
            </footer>
        </div>

        <script>
            const menuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (menuButton && mobileMenu) {
                menuButton.addEventListener('click', () => {
                    const isHidden = mobileMenu.classList.contains('hidden');
                    if (isHidden) {
                        mobileMenu.classList.remove('hidden');
                        menuButton.setAttribute('aria-expanded', 'true');
                    } else {
                        mobileMenu.classList.add('hidden');
                        menuButton.setAttribute('aria-expanded', 'false');
                    }
                });

                document.querySelectorAll('.mobile-menu-link').forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                        menuButton.setAttribute('aria-expanded', 'false');
                    });
                });
            }
        </script>
    </body>
</html>
