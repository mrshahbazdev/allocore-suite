<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 {{ ($theme ?? 'light') === 'dark' ? 'dark' : '' }}">
    @php
        $brand = config('app.team_branding') ?? ['name' => \App\Models\SiteSetting::value('site_name', config('app.name', 'Allocore Suite')), 'logo' => null, 'favicon' => null, 'primary_color' => null, 'accent_color' => null];
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @if ($brand['favicon'])
            <link rel="icon" href="{{ $brand['favicon'] }}">
        @else
            <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
            <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        @endif

        <title>{{ $brand['name'] }}</title>
        <meta name="description" content="{{ __('landing.meta.description') }}">
        <meta name="keywords" content="{{ __('landing.meta.keywords') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans text-slate-600 antialiased">
        <div class="min-h-screen bg-slate-50">
            <div class="mx-auto grid min-h-screen w-full max-w-7xl items-center gap-0 px-6 py-10 lg:grid-cols-[1.05fr_480px] lg:px-8 lg:py-0">
                {{-- Left: branding --}}
                <div class="hidden flex-col justify-center lg:flex lg:pr-16">
                    <a href="/" class="mb-8 inline-flex items-center gap-3">
                        <img src="{{ $brand['logo'] ?? asset('logo-mark.png') }}" alt="" class="h-12 w-12 object-contain rounded-2xl bg-white">
                        <span class="text-2xl font-bold text-slate-900">{{ $brand['name'] }}</span>
                    </a>
                    <h1 class="max-w-xl text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl">
                        {{ __('auth.guest.heading') }}
                    </h1>
                    <p class="mt-6 max-w-lg text-lg leading-8 text-slate-600">
                        {{ __('auth.guest.subheading') }}
                    </p>

                    <div class="mt-10 grid max-w-md gap-4">
                        @foreach ([
                            ['icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 015.106 15M15 19.128v.106A12.318 12.318 0 015.106 15m0 0a9.333 9.333 0 00-2.646-5.609T5 9.75a9.375 9.375 0 00-2.25 5.25m0 0v-.106A12.318 12.318 0 015.106 15', 'title' => __('Score')],
                            ['icon' => 'M18 18.72v.48a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-.48c0-.66.34-1.26.88-1.61l.28-.17a.75.75 0 00.25-1.03l-.11-.18a3.75 3.75 0 00-6.5 0l-.11.18a.75.75 0 00.25 1.03l.28.17c.54.35.88.95.88 1.61v.48m0 0a.75.75 0 00-1.5 0m0 0a.75.75 0 01-1.5 0m0 0a.75.75 0 01-1.5 0v-.48c0-.66.34-1.26.88-1.61l.28-.17a.75.75 0 00.25-1.03l-.11-.18a3.75 3.75 0 00-6.5 0l-.11.18a.75.75 0 00.25 1.03l.28.17c.54.35.88.95.88 1.61v.48m0 0v.48a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v-.48', 'title' => __('Teams')],
                            ['icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z', 'title' => __('Data')],
                        ] as $item)
                            <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: {{ $brand['primary_color'] ? $brand['primary_color'].'20' : '#e0f2fe20' }}; color: {{ $brand['primary_color'] ?? '#0ea5e9' }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"></path></svg>
                                </div>
                                <div class="text-base font-semibold text-slate-900">{{ $item['title'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: auth card --}}
                <div class="relative w-full">
                    <div class="mb-6 flex items-center justify-between lg:hidden">
                        <a href="/" class="inline-flex items-center gap-3">
                            <img src="{{ $brand['logo'] ?? asset('logo-mark.png') }}" alt="" class="h-11 w-11 object-contain rounded-2xl bg-white">
                            <span class="text-xl font-bold text-slate-900">{{ $brand['name'] }}</span>
                        </a>
                        @include('partials.locale-switcher')
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg sm:p-10">
                        <div class="hidden justify-end lg:flex">
                            @include('partials.locale-switcher')
                        </div>

                        @yield('content')
                        {{ $slot ?? '' }}
                    </div>
                </div>
            </div>
        </div>

        @include('partials.cookie-consent')
        @livewireScripts
    </body>
</html>
