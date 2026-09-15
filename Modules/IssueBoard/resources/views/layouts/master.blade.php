<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('issueboard::issueboard.board_title') }}</title>

    {{-- Wenn Allocore bereits ein App-Layout hat: dieses File loeschen und in den
         Livewire-Komponenten ->layout('layouts.app') setzen. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script defer src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">

<div class="min-h-full">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-[1600px] items-center justify-between gap-4 px-6 py-4">
            <a href="{{ route('issueboard.index') }}" class="text-base font-semibold tracking-tight text-slate-900">
                {{ __('issueboard::issueboard.board_title') }}
            </a>

            <a href="{{ route('issueboard.create') }}"
               class="rounded-md bg-slate-900 px-3.5 py-2 text-sm font-medium text-white transition hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                {{ __('issueboard::issueboard.new_issue') }}
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-[1600px] px-6 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</div>

<div x-data="{ show: false, message: '' }"
     x-on:board-updated.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 2500)"
     x-on:board-error.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 4000)"
     x-show="show"
     x-transition.opacity
     x-cloak
     class="fixed bottom-6 left-1/2 -translate-x-1/2 rounded-md bg-slate-900 px-4 py-2.5 text-sm text-white shadow-lg"
     role="status"
     aria-live="polite">
    <span x-text="message"></span>
</div>

@livewireScripts
</body>
</html>
