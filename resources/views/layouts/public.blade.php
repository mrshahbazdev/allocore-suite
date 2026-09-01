<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', \App\Models\SiteSetting::value('site_name', config('app.name', 'Allocore Suite')))</title>
    <meta name="description" content="@yield('meta_description', '')">
    <meta name="keywords" content="@yield('meta_keywords', '')">
    <meta property="og:title" content="@yield('og_title', '')">
    <meta property="og:description" content="@yield('og_description', '')">
    <meta property="og:image" content="@yield('og_image', '')">

    @stack('meta')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .glossary-link {
            display: inline !important;
            font-weight: 600 !important;
            color: #475569 !important;
            text-decoration: underline dashed 1.5px !important;
            text-decoration-color: #94a3b8 !important;
            text-underline-offset: 4px !important;
            cursor: pointer !important;
            padding: 1px 3px !important;
            border-radius: 4px !important;
            transition: all 0.2s ease-in-out !important;
        }
        .glossary-link:hover {
            color: #0f172a !important;
            background-color: rgba(100, 116, 139, 0.12) !important;
            text-decoration-style: solid !important;
            text-decoration-color: #475569 !important;
        }
        [style*="background-color"] .glossary-link,
        [style*="bg-"] .glossary-link,
        .bg-slate-900 .glossary-link,
        .bg-slate-800 .glossary-link,
        .bg-slate-950 .glossary-link,
        .dark .glossary-link {
            color: #e2e8f0 !important;
            text-decoration-color: #cbd5e1 !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
        [style*="background-color"] .glossary-link:hover,
        [style*="bg-"] .glossary-link:hover,
        .bg-slate-900 .glossary-link:hover,
        .bg-slate-800 .glossary-link:hover,
        .bg-slate-950 .glossary-link:hover,
        .dark .glossary-link:hover {
            color: #ffffff !important;
            text-decoration-color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.2) !important;
        }
    </style>
</head>
<body class="h-full font-sans text-slate-600 antialiased">
    <div class="flex min-h-full flex-col bg-slate-50">
        @include('partials.public-header')

        <main class="flex-1">
            @yield('content')
        </main>

        @include('partials.public-footer')
    </div>
</body>
</html>
