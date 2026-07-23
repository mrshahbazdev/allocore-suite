<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Install') }} — Allocore Suite</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full font-sans antialiased">
    <div class="flex min-h-full flex-col items-center justify-center p-4">
        <div class="w-full max-w-xl rounded-2xl bg-white p-8 shadow-xl border border-slate-200">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Allocore Suite Installer') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Set up your workspace in a few steps.') }}</p>
            </div>

            @if (session('error'))
                <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>
