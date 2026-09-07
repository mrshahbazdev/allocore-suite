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
    @stack('styles')
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

        /* On colored / teal / dark sections (Banner Text Blocks) */
        .is-dark-section .glossary-link,
        section[style*="background-color"] .prose .glossary-link,
        section[style*="background-color"] > div > p .glossary-link,
        .prose[style*="color: rgb(255"] .glossary-link,
        .prose[style*="color: #fff"] .glossary-link,
        .prose[style*="color:#fff"] .glossary-link,
        .dark:not(.bg-white) .glossary-link {
            color: #e2e8f0 !important;
            text-decoration: underline dashed 1.5px !important;
            text-decoration-color: #cbd5e1 !important;
            background-color: rgba(255, 255, 255, 0.12) !important;
        }
        .is-dark-section .glossary-link:hover,
        section[style*="background-color"] .prose .glossary-link:hover,
        section[style*="background-color"] > div > p .glossary-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.25) !important;
            text-decoration-style: solid !important;
            text-decoration-color: #ffffff !important;
        }

        /* Typography & Rich Text Formatting for Pages, Blog & Custom HTML */
        .prose, .page-content-body, .blog-content-body, .rich-text-content {
            color: #334155;
            line-height: 1.75;
            font-size: 1.0625rem;
        }

        .prose h1, .page-content-body h1, .blog-content-body h1, .rich-text-content h1 {
            font-size: 2.25rem !important;
            line-height: 1.25 !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            margin-top: 2rem !important;
            margin-bottom: 1rem !important;
            letter-spacing: -0.025em !important;
        }

        .prose h2, .page-content-body h2, .blog-content-body h2, .rich-text-content h2 {
            font-size: 1.75rem !important;
            line-height: 1.3 !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin-top: 1.75rem !important;
            margin-bottom: 0.75rem !important;
            letter-spacing: -0.02em !important;
        }

        .prose h3, .page-content-body h3, .blog-content-body h3, .rich-text-content h3 {
            font-size: 1.375rem !important;
            line-height: 1.4 !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            margin-top: 1.5rem !important;
            margin-bottom: 0.5rem !important;
        }

        .prose h4, .page-content-body h4, .blog-content-body h4, .rich-text-content h4 {
            font-size: 1.15rem !important;
            line-height: 1.4 !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            margin-top: 1.25rem !important;
            margin-bottom: 0.5rem !important;
        }

        .prose h5, .page-content-body h5, .blog-content-body h5, .rich-text-content h5 {
            font-size: 1rem !important;
            font-weight: 600 !important;
            color: #334155 !important;
            margin-top: 1rem !important;
            margin-bottom: 0.25rem !important;
        }

        .prose h6, .page-content-body h6, .blog-content-body h6, .rich-text-content h6 {
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            color: #475569 !important;
            text-transform: uppercase !important;
            margin-top: 1rem !important;
            margin-bottom: 0.25rem !important;
        }

        .prose p, .page-content-body p, .blog-content-body p, .rich-text-content p {
            margin-top: 1rem !important;
            margin-bottom: 1rem !important;
        }

        .prose strong, .prose b, .page-content-body strong, .page-content-body b, .blog-content-body strong, .blog-content-body b, .rich-text-content strong, .rich-text-content b {
            font-weight: 700 !important;
            color: #0f172a !important;
        }

        .prose em, .prose i, .page-content-body em, .page-content-body i, .blog-content-body em, .blog-content-body i, .rich-text-content em, .rich-text-content i {
            font-style: italic !important;
        }

        .prose u, .page-content-body u, .blog-content-body u, .rich-text-content u {
            text-decoration: underline !important;
            text-underline-offset: 2px !important;
        }

        .prose s, .prose strike, .prose del, .page-content-body s, .blog-content-body s, .rich-text-content s {
            text-decoration: line-through !important;
            color: #64748b !important;
        }

        .prose a:not(.glossary-link), .page-content-body a:not(.glossary-link), .blog-content-body a:not(.glossary-link), .rich-text-content a:not(.glossary-link) {
            color: #ff9200 !important;
            font-weight: 600 !important;
            text-decoration: underline !important;
            text-decoration-color: rgba(255, 146, 0, 0.4) !important;
            text-underline-offset: 3px !important;
            transition: all 0.15s ease-in-out !important;
        }

        .prose a:not(.glossary-link):hover, .page-content-body a:not(.glossary-link):hover, .blog-content-body a:not(.glossary-link):hover, .rich-text-content a:not(.glossary-link):hover {
            color: #e68300 !important;
            text-decoration-color: #e68300 !important;
        }

        .prose ul, .page-content-body ul, .blog-content-body ul, .rich-text-content ul {
            list-style-type: disc !important;
            padding-left: 1.75rem !important;
            margin-top: 1rem !important;
            margin-bottom: 1rem !important;
        }

        .prose ol, .page-content-body ol, .blog-content-body ol, .rich-text-content ol {
            list-style-type: decimal !important;
            padding-left: 1.75rem !important;
            margin-top: 1rem !important;
            margin-bottom: 1rem !important;
        }

        .prose li, .page-content-body li, .blog-content-body li, .rich-text-content li {
            margin-top: 0.35rem !important;
            margin-bottom: 0.35rem !important;
            line-height: 1.65 !important;
        }

        .prose blockquote, .page-content-body blockquote, .blog-content-body blockquote, .rich-text-content blockquote {
            border-left: 4px solid #ff9200 !important;
            background: #fffaf0 !important;
            padding: 0.875rem 1.25rem !important;
            margin: 1.5rem 0 !important;
            border-radius: 0 0.5rem 0.5rem 0 !important;
            color: #475569 !important;
            font-style: italic !important;
        }

        .prose table, .page-content-body table, .blog-content-body table, .rich-text-content table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 1.5rem 0 !important;
            font-size: 0.9375rem !important;
        }

        .prose th, .page-content-body th, .blog-content-body th, .rich-text-content th {
            border: 1px solid #cbd5e1 !important;
            background-color: #f8fafc !important;
            padding: 0.625rem 0.875rem !important;
            font-weight: 600 !important;
            text-align: left !important;
            color: #0f172a !important;
        }

        .prose td, .page-content-body td, .blog-content-body td, .rich-text-content td {
            border: 1px solid #e2e8f0 !important;
            padding: 0.625rem 0.875rem !important;
        }

        .prose code, .page-content-body code, .blog-content-body code, .rich-text-content code {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            padding: 0.2rem 0.45rem !important;
            border-radius: 0.25rem !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            font-size: 0.875em !important;
        }

        .prose pre, .page-content-body pre, .blog-content-body pre, .rich-text-content pre {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            padding: 1rem 1.25rem !important;
            border-radius: 0.5rem !important;
            overflow-x: auto !important;
            margin: 1.5rem 0 !important;
        }

        .prose pre code, .page-content-body pre code, .blog-content-body pre code, .rich-text-content pre code {
            background-color: transparent !important;
            color: inherit !important;
            padding: 0 !important;
        }

        .prose img, .page-content-body img, .blog-content-body img, .rich-text-content img {
            max-width: 100% !important;
            height: auto !important;
            border-radius: 0.75rem !important;
            margin: 1.5rem 0 !important;
        }

        .prose hr, .page-content-body hr, .blog-content-body hr, .rich-text-content hr {
            border: none !important;
            border-top: 1px solid #e2e8f0 !important;
            margin: 2rem 0 !important;
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
    @stack('scripts')
</body>
</html>
