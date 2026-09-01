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
            color: #334155 !important;
            text-decoration: underline dashed 1.5px !important;
            text-decoration-color: #64748b !important;
            text-underline-offset: 4px !important;
            cursor: pointer !important;
            padding: 1px 3px !important;
            border-radius: 4px !important;
            background-color: rgba(100, 116, 139, 0.08) !important;
            transition: all 0.2s ease-in-out !important;
        }
        .glossary-link:hover {
            color: #0f172a !important;
            background-color: rgba(100, 116, 139, 0.2) !important;
            text-decoration-style: solid !important;
            text-decoration-color: #0f172a !important;
        }

        /* Inside ANY white card or container on any section */
        .bg-white .glossary-link,
        .card .glossary-link,
        [class*="bg-white"] .glossary-link {
            color: #334155 !important;
            text-decoration-color: #64748b !important;
            background-color: rgba(100, 116, 139, 0.08) !important;
        }
        .bg-white .glossary-link:hover,
        .card .glossary-link:hover,
        [class*="bg-white"] .glossary-link:hover {
            color: #0f172a !important;
            background-color: rgba(100, 116, 139, 0.2) !important;
            text-decoration-color: #0f172a !important;
        }

        /* Only when directly on dark text block with no white card */
        .prose[style*="color: rgb(255"] .glossary-link,
        .prose[style*="color: #fff"] .glossary-link,
        .prose[style*="color:#fff"] .glossary-link,
        .dark:not(.bg-white) .glossary-link {
            color: #f1f5f9 !important;
            text-decoration-color: #cbd5e1 !important;
            background-color: rgba(255, 255, 255, 0.15) !important;
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
