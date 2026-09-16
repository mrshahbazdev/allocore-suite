@extends('layouts.shell')

@section('content')
    <!-- Header -->
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ __('Meine Werkzeuge & Suite') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Alle in Ihrem Abonnement enthaltenen Unternehmer-Werkzeuge auf einen Blick.') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ $accessible->count() }} {{ __('Werkzeuge aktiv freigeschaltet') }}
            </span>
            @if ($locked->isNotEmpty())
                <a href="{{ route('billing.plans') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">{{ __('Upgrade / Pläne') }}</a>
            @endif
        </div>
    </div>

    <!-- Category Mapping for Icons & Colors -->
    @php
        $categoryStyles = [
            'Finanzen' => [
                'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'emerald',
                'bg' => 'bg-emerald-50 text-emerald-600',
                'badge' => 'bg-emerald-100 text-emerald-800',
            ],
            'Vertrieb & Marketing' => [
                'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
                'color' => 'indigo',
                'bg' => 'bg-indigo-50 text-indigo-600',
                'badge' => 'bg-indigo-100 text-indigo-800',
            ],
            'Produktivität & Prozesse' => [
                'icon' => 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z',
                'color' => 'blue',
                'bg' => 'bg-blue-50 text-blue-600',
                'badge' => 'bg-blue-100 text-blue-800',
            ],
            'Führung & Strategie' => [
                'icon' => 'M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z',
                'color' => 'purple',
                'bg' => 'bg-purple-50 text-purple-600',
                'badge' => 'bg-purple-100 text-purple-800',
            ],
            'Wissen & Bildung' => [
                'icon' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.967 8.967 0 00-6 2.292m6-2.292v14.25m0-14.25A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
                'color' => 'amber',
                'bg' => 'bg-amber-50 text-amber-700',
                'badge' => 'bg-amber-100 text-amber-900',
            ],
        ];
    @endphp

    <!-- Accessible Tools Grouped by Category -->
    @if ($accessible->isNotEmpty())
        <div class="space-y-10">
            @foreach ($categorizedAccessible as $categoryName => $tools)
                @php
                    $style = $categoryStyles[$categoryName] ?? [
                        'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
                        'bg' => 'bg-slate-100 text-slate-700',
                        'badge' => 'bg-slate-100 text-slate-800',
                    ];
                @endphp

                <section>
                    <div class="mb-4 flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $style['bg'] }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $style['icon'] }}" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900">{{ __($categoryName) }}</h2>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">{{ $tools->count() }}</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($tools as $tool)
                            <div class="group relative flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md">
                                <div>
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $style['bg'] }} transition group-hover:scale-105">
                                                @include('tools.partials.icon', ['icon' => $tool->icon ?: 'sparkles', 'class' => 'h-5 w-5'])
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-slate-900 text-sm leading-snug group-hover:text-indigo-600 transition">{{ $tool->name }}</h3>
                                                @if ($tool->badge_text)
                                                    <span class="inline-block mt-0.5 rounded-md {{ $style['badge'] }} px-1.5 py-0.2 text-[10px] font-bold uppercase tracking-wider">{{ $tool->badge_text }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <p class="mt-3 text-xs leading-relaxed text-slate-600 line-clamp-3">
                                        {{ $tool->description }}
                                    </p>
                                </div>

                                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        {{ __('Freigeschaltet') }}
                                    </span>

                                    <a href="{{ url('app/'.$tool->route_prefix) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-600 transition">
                                        {{ __('Öffnen') }}
                                        <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    <!-- Locked Tools Section (if any) -->
    @if ($locked->isNotEmpty())
        <div class="mt-12 pt-8 border-t border-slate-200">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900">{{ __('Weitere optionale Spezial-Werkzeuge') }} ({{ $locked->count() }})</h2>
                <p class="text-xs text-slate-500">{{ __('Diese Werkzeuge können separat freigeschaltet oder als Zusatzmodul gebucht werden.') }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($locked as $tool)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 opacity-85">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-bold text-slate-800 text-sm">{{ $tool->name }}</h3>
                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-600">{{ __('Optional') }}</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 line-clamp-2">{{ $tool->description }}</p>
                        <div class="mt-4 pt-3 border-t border-slate-200/60 flex justify-end">
                            <a href="{{ route('billing.plans', ['module' => $tool->key]) }}" class="inline-flex items-center rounded-lg border border-indigo-600 px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 transition">{{ __('Freischalten') }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
