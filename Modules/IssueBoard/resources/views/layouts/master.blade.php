<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ff9200">
    <title>{{ $title ?? __('issueboard::issueboard.board_title') }} · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script defer src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full bg-slate-50 font-sans text-slate-800 antialiased">
<div class="min-h-screen">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-[1600px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <a href="{{ route('dashboard') }}"
                   class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-900 shadow-sm transition hover:bg-slate-700"
                   aria-label="{{ __('issueboard::issueboard.back_to_allocore') }}">
                    <img src="{{ asset('logo-mark.png') }}" class="h-7 w-7 object-contain" alt="">
                </a>
                <div class="min-w-0">
                    <a href="{{ route('issueboard.index') }}" class="block truncate text-sm font-bold text-slate-900 hover:text-[#d97706]">
                        {{ __('issueboard::issueboard.board_title') }}
                    </a>
                    <p class="hidden truncate text-xs text-slate-500 sm:block">
                        {{ __('issueboard::issueboard.header_description') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <span class="hidden text-sm font-medium text-slate-500 lg:inline">{{ auth()->user()->name }}</span>
                @endauth
                <a href="{{ route('issueboard.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#ff9200] px-3.5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#e68200] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ff9200] sm:px-4">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('issueboard::issueboard.new_issue') }}</span>
                    <span class="sm:hidden">{{ __('issueboard::issueboard.new_short') }}</span>
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        @if (session('status'))
            <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</div>

<div x-data="{ show: false, message: '', error: false }"
     x-on:board-updated.window="message = $event.detail.message; error = false; show = true; setTimeout(() => show = false, 2500)"
     x-on:board-error.window="message = $event.detail.message; error = true; show = true; setTimeout(() => show = false, 4000)"
     x-show="show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="translate-y-2 opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-2 opacity-0"
     x-cloak
     :class="error ? 'bg-rose-700' : 'bg-slate-900'"
     class="fixed bottom-6 left-1/2 z-50 flex max-w-[calc(100%-2rem)] -translate-x-1/2 items-center gap-2 rounded-xl px-4 py-3 text-sm font-medium text-white shadow-xl"
     role="status"
     aria-live="polite">
    <span class="h-2 w-2 rounded-full bg-current opacity-70"></span>
    <span x-text="message"></span>
</div>

@livewireScripts
</body>
</html>
