<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Allocore Suite') }}</title>
        <meta name="description" content="One platform for every tool your team subscribes to. Central auth, teams, billing, modules, and analytics in a single shell.">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans text-slate-100 antialiased">
        @php($modules = \App\Models\Module::where('is_active', true)->get())

        <div class="relative isolate overflow-hidden bg-slate-950">
            {{-- Background gradients --}}
            <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[40rem] bg-[radial-gradient(ellipse_at_top,_rgba(99,102,241,0.28),_transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[linear-gradient(rgba(148,163,184,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.05)_1px,transparent_1px)] bg-[size:64px_64px]"></div>

            {{-- Header --}}
            <header class="absolute inset-x-0 top-0 z-50">
                <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8" aria-label="Global">
                    <a href="/" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500 text-lg font-black text-white shadow-lg shadow-indigo-500/30">A</div>
                        <span class="text-lg font-bold text-white">Allocore Suite</span>
                    </a>

                    <div class="hidden lg:flex lg:items-center lg:gap-8">
                        <a href="#features" class="text-sm font-medium text-slate-300 hover:text-white">Features</a>
                        <a href="#modules" class="text-sm font-medium text-slate-300 hover:text-white">Modules</a>
                        <a href="{{ route('billing.plans') }}" class="text-sm font-medium text-slate-300 hover:text-white">Pricing</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-300 hover:text-white">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="rounded-full bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-400">Get started</a>
                            @endif
                        @endauth
                    </div>

                    <button type="button" id="mobile-menu-button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-slate-300 lg:hidden" aria-expanded="false" aria-controls="mobile-menu">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </nav>

                {{-- Mobile menu --}}
                <div id="mobile-menu" class="hidden lg:hidden">
                    <div class="mx-auto max-w-7xl px-6 py-4">
                        <div class="rounded-2xl border border-white/10 bg-slate-900/90 p-4 shadow-2xl backdrop-blur">
                            <a href="#features" class="mobile-menu-link block rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/5 hover:text-white">Features</a>
                            <a href="#modules" class="mobile-menu-link block rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/5 hover:text-white">Modules</a>
                            <a href="{{ route('billing.plans') }}" class="block rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/5 hover:text-white">Pricing</a>
                            <div class="mt-4 flex flex-col gap-2 border-t border-white/10 pt-4">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="rounded-xl bg-indigo-500 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-indigo-400">Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="rounded-xl px-4 py-3 text-center text-sm font-medium text-slate-300 hover:bg-white/5">Log in</a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="rounded-xl bg-indigo-500 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-indigo-400">Get started</a>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Hero --}}
            <main>
                <section class="relative pb-20 pt-36 lg:pt-44">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="mx-auto max-w-3xl text-center">
                            <div class="inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-4 py-1.5 text-sm text-indigo-200">
                                <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span>
                                Modular SaaS workspace
                            </div>
                            <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-6xl lg:text-7xl">
                                One platform for every tool your team subscribes to.
                            </h1>
                            <p class="mt-6 text-lg leading-8 text-slate-300 sm:text-xl">
                                Allocore Suite unites central auth, team management, module gating, billing, and analytics into a single, consistent shell.
                            </p>
                            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="rounded-full bg-white px-7 py-3.5 text-base font-semibold text-slate-950 shadow-lg hover:bg-slate-200">Open dashboard</a>
                                @else
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="rounded-full bg-white px-7 py-3.5 text-base font-semibold text-slate-950 shadow-lg hover:bg-slate-200">Start free</a>
                                    @endif
                                    <a href="{{ route('login') }}" class="rounded-full border border-white/15 bg-white/5 px-7 py-3.5 text-base font-semibold text-white hover:bg-white/10">Log in</a>
                                @endauth
                            </div>
                        </div>

                        {{-- Stats strip --}}
                        <div class="mx-auto mt-16 grid max-w-4xl gap-4 sm:grid-cols-3">
                            @foreach ([
                                ['label' => 'One login', 'value' => 'Central auth'],
                                ['label' => 'Shared access', 'value' => 'Teams + billing'],
                                ['label' => 'One bill', 'value' => 'Per user / team'],
                            ] as $stat)
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-center backdrop-blur">
                                    <div class="text-xs font-semibold uppercase tracking-wider text-indigo-300">{{ $stat['label'] }}</div>
                                    <div class="mt-2 text-xl font-bold text-white">{{ $stat['value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Features --}}
                <section id="features" class="relative py-24">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Built for multi-tool SaaS teams</h2>
                            <p class="mt-4 text-lg text-slate-400">Everything you need to onboard customers, manage access, and ship connected products from one admin panel.</p>
                        </div>

                        <div class="mx-auto mt-16 grid max-w-5xl gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ([
                                ['icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.637-2.911M15 19.128V13.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 13.5v3.75m-3-1.837a6.375 6.375 0 0111.637-2.911c1.249 1.037 2.058 2.451 2.305 3.97', 'title' => 'Central auth', 'desc' => 'Single sign-on across all modules with role-based access.'],
                                ['icon' => 'M18 18.72v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48c0-.66.34-1.26.88-1.61l.28-.17a.75.75 0 00.25-1.03l-.11-.18a3.75 3.75 0 00-6.5 0l-.11.18a.75.75 0 00.25 1.03l.28.17c.54.35.88.95.88 1.61v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48a2.25 2.25 0 00-3-2.12 2.25 2.25 0 00-1.5 2.12v.48a.75.75 0 01-.75.75H3a.75.75 0 01-.75-.75v-.48a6.75 6.75 0 0111.25-5.07 6.75 6.75 0 019.75 5.07zM12 12a3 3 0 100-6 3 3 0 000 6z', 'title' => 'Team workspaces', 'desc' => 'Create teams, invite members, and switch context instantly.'],
                                ['icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z', 'title' => 'Billing & plans', 'desc' => 'Stripe, PayPal, and bank-transfer subscriptions with plan gating.'],
                                ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', 'title' => 'Analytics dashboard', 'desc' => 'Combined insights from every subscribed module in one place.'],
                            ] as $feature)
                                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 backdrop-blur transition hover:border-indigo-400/30 hover:bg-slate-900">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/></svg>
                                    </div>
                                    <h3 class="mt-4 text-lg font-semibold text-white">{{ $feature['title'] }}</h3>
                                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $feature['desc'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Modules --}}
                <section id="modules" class="relative py-24">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Connected modules</h2>
                            <p class="mt-4 text-lg text-slate-400">Unlock the tools your team needs. Each module shares the same auth, team, and billing layer.</p>
                        </div>

                        <div class="mx-auto mt-16 grid max-w-5xl gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @forelse ($modules as $module)
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur transition hover:border-indigo-400/30">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-white">{{ $module->name }}</h3>
                                            <p class="mt-2 text-sm text-slate-400">{{ $module->description }}</p>
                                        </div>
                                        <span class="inline-flex shrink-0 rounded-full bg-emerald-400/10 px-2.5 py-1 text-xs font-medium text-emerald-300">Ready</span>
                                    </div>
                                    <div class="mt-6 flex items-center gap-2 text-sm font-medium text-indigo-300">
                                        <span>{{ $module->route_prefix }}</span>
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full rounded-2xl border border-white/10 bg-white/5 p-8 text-center text-slate-400">
                                    New modules are added as your product suite grows.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                {{-- CTA --}}
                <section class="relative py-24">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="relative overflow-hidden rounded-3xl border border-indigo-500/20 bg-indigo-600/10 px-6 py-16 text-center backdrop-blur sm:px-16">
                            <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
                            <div class="pointer-events-none absolute -bottom-20 -right-20 h-64 w-64 rounded-full bg-violet-500/20 blur-3xl"></div>
                            <h2 class="relative text-3xl font-bold tracking-tight text-white sm:text-4xl">Start using Allocore Suite today</h2>
                            <p class="relative mx-auto mt-4 max-w-xl text-lg text-indigo-100">Create an account, invite your team, and subscribe to the modules that power your business.</p>
                            <div class="relative mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="rounded-full bg-white px-7 py-3.5 text-base font-semibold text-slate-950 shadow-lg hover:bg-slate-200">Open dashboard</a>
                                @else
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="rounded-full bg-white px-7 py-3.5 text-base font-semibold text-slate-950 shadow-lg hover:bg-slate-200">Create free account</a>
                                    @endif
                                    <a href="{{ route('billing.plans') }}" class="rounded-full border border-white/20 bg-white/5 px-7 py-3.5 text-base font-semibold text-white hover:bg-white/10">View pricing</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            {{-- Footer --}}
            <footer class="border-t border-white/10 bg-slate-950 py-12">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500 text-base font-black text-white">A</div>
                            <span class="text-sm font-semibold text-white">Allocore Suite</span>
                        </div>
                        <div class="flex items-center gap-6 text-sm text-slate-400">
                            <a href="{{ route('login') }}" class="hover:text-white">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="hover:text-white">Sign up</a>
                            @endif
                            <a href="{{ route('billing.plans') }}" class="hover:text-white">Pricing</a>
                        </div>
                        <p class="text-xs text-slate-500">&copy; {{ date('Y') }} Allocore Suite. All rights reserved.</p>
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
