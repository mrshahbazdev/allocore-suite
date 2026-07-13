<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Allocore Suite') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans text-slate-100 antialiased">
        <div class="min-h-full bg-[radial-gradient(circle_at_top,_rgba(79,70,229,0.22),_transparent_42%),linear-gradient(180deg,_#020617_0%,_#111827_100%)]">
            <div class="mx-auto grid min-h-screen w-full max-w-7xl items-center gap-10 px-6 py-10 lg:grid-cols-[1fr_420px] lg:px-8">
                <div class="hidden lg:block">
                    <div class="max-w-xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1 text-sm text-slate-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Allocore Suite
                        </div>
                        <h1 class="mt-6 text-5xl font-black tracking-tight text-white">
                            Clean authentication for a modular SaaS workspace.
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-slate-300">
                            Sign in to reach your subscribed tools, team dashboards, and admin controls in a single consistent shell.
                        </p>
                        <div class="mt-8 grid gap-4 sm:grid-cols-3">
                            @foreach ([
                                'Central auth',
                                'Teams + billing',
                                'Role-based admin',
                            ] as $item)
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div class="text-sm font-medium text-white">{{ $item }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-white px-6 py-8 text-slate-900 shadow-2xl shadow-slate-950/30 sm:px-8">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-500 text-lg font-black text-white">A</div>
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Allocore Suite</div>
                            <div class="text-xs text-slate-500">Sign in to continue</div>
                        </div>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
