<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('You are offline') }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <div class="flex min-h-screen items-center justify-center p-6">
        <div class="max-w-md text-center">
            <h1 class="text-3xl font-bold text-indigo-600">{{ __('You are offline') }}</h1>
            <p class="mt-4 text-slate-600">{{ __('Please check your connection and try again.') }}</p>
            <a href="/" class="mt-6 inline-block rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Retry') }}</a>
        </div>
    </div>
</body>
</html>
