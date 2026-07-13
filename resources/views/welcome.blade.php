<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Allocore Suite') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans text-slate-100 antialiased">
        @php($modules = \App\Models\Module::where('is_active', true)->get())

        <div class="relative isolate overflow-hidden">
            <div class="absolute inset-x-0 top-0 -z-10 h-[36rem] bg-[radial-gradient(circle_at_top,_rgba(79,70,229,0.28),_transparent_45%),linear-gradient(180deg,_#020617_0%,_#0f172a_55%,_#111827_100%)]"></div>
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(rgba(148,163,184,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.06)_1px,transparent_1px)] bg-[size:72px_72px]"></div>

            <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-500 text-lg font-black text-white shadow-lg shadow-indigo-500/30">A</div>
                    <div>
                        <div class="text-sm font-medium text-slate-300">Allocore Suite</div>
                        <div class="text-xs text-slate-400">Modules, billing, analytics, teams</div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-full bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-400">Get started</a>
                        @endif
                    @endauth
                </div>
            </header>

            <main class="mx-auto grid w-full max-w-7xl gap-16 px-6 pb-20 pt-10 lg:grid-cols-[1.15fr_.85fr] lg:px-8 lg:pt-16">
                <section class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-4 py-1 text-sm text-indigo-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Built for central auth, teams, subscriptions, and modules
                    </div>

                    <h1 class="mt-6 text-5xl font-black tracking-tight text-white sm:text-6xl">
                        One platform for every tool your team subscribes to.
                    </h1>

                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                        Allocore Suite brings your products into one shared shell: central login, team ownership, module gating, billing, and a combined analytics overview.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-950 hover:bg-slate-200">Open dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-950 hover:bg-slate-200">Start free</a>
                            <a href="{{ route('login') }}" class="rounded-full border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">Log in</a>
                        @endauth
                    </div>

                    <div class="mt-12 grid gap-4 sm:grid-cols-3">
                        @foreach ([
                            ['label' => 'Central auth', 'value' => 'One login'],
                            ['label' => 'Teams', 'value' => 'Shared access'],
                            ['label' => 'Billing', 'value' => 'Per user / team'],
                        ] as $stat)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <div class="text-sm text-slate-400">{{ $stat['label'] }}</div>
                                <div class="mt-2 text-2xl font-bold text-white">{{ $stat['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <aside class="space-y-5">
                    <div class="rounded-3xl border border-white/10 bg-white/8 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
                        <div class="text-sm font-medium text-indigo-200">Overview dashboard</div>
                        <h2 class="mt-2 text-2xl font-bold text-white">Combined analytics from every subscribed module</h2>
                        <div class="mt-6 grid gap-3">
                            @foreach ($modules as $module)
                                <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/30 px-4 py-3">
                                    <div>
                                        <div class="font-semibold text-white">{{ $module->name }}</div>
                                        <div class="text-sm text-slate-400">{{ $module->description }}</div>
                                    </div>
                                    <span class="rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-semibold text-emerald-300">Ready</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1">
                        <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 backdrop-blur">
                            <div class="text-sm font-medium text-slate-400">Modules</div>
                            <div class="mt-2 text-3xl font-black text-white">{{ $modules->count() }}</div>
                            <p class="mt-2 text-sm leading-6 text-slate-300">Audit, finance, invoicing, and lead tooling all live in one shell.</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 backdrop-blur">
                            <div class="text-sm font-medium text-slate-400">Access control</div>
                            <div class="mt-2 text-3xl font-black text-white">Module gated</div>
                            <p class="mt-2 text-sm leading-6 text-slate-300">Users only see tools included in their active user or team subscription.</p>
                        </div>
                    </div>
                </aside>
            </main>
        </div>
    </body>
</html>
