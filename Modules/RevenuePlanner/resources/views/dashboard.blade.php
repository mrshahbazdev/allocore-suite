@php $title = __('Umsatzplaner — Dashboard'); @endphp
@extends('layouts.shell')

@section('content')
    @include('revenueplanner::partials.nav')

    <div class="space-y-6" data-no-navigate>
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800">
                {{ session('warning') }}
            </div>
        @endif

        @if (! $activePlan)
            <!-- Empty State / Welcome Hero -->
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-50 text-3xl">
                    🎯
                </div>
                <h2 class="mt-4 text-2xl font-bold text-slate-900">{{ __('Willkommen beim Allocore Umsatzplaner') }}</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm text-slate-500 leading-relaxed">
                    {{ __('Ermitteln Sie in 5 einfachen Schritten Ihren tatsächlich benötigten Mindestumsatz, analysieren Sie Ihre Umsatzlücke und berechnen Sie den exakten Neukundenbedarf für Ihr Unternehmen.') }}
                </p>
                <div class="mt-6 flex justify-center gap-4">
                    <a href="{{ route('revenueplanner.wizard') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#ff9200] px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-orange-600">
                        ⚡ {{ __('Jetzt ersten Umsatzplan erstellen') }}
                    </a>
                </div>
            </div>
        @else
            <!-- Active Plan KPI Summary Header -->
            <div class="rounded-3xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-8 text-white shadow-lg">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-300">
                            <span>📌</span> {{ __('Aktiver Plan: :year', ['year' => $activePlan->fiscal_year]) }} — {{ $activePlan->name }}
                        </div>
                        <h1 class="mt-2 text-3xl font-black">{{ number_format($activePlan->target_revenue_annual, 0, ',', '.') }} € <span class="text-base font-normal text-slate-400">/ {{ __('Jahr Zielumsatz') }}</span></h1>
                        <p class="text-xs text-slate-300 mt-1">
                            {{ __('Monatlicher Zielumsatz:') }} <strong>{{ number_format($activePlan->target_revenue_monthly, 0, ',', '.') }} €</strong> | 
                            {{ __('Deckungsbeitragsmarge:') }} <strong>{{ $activePlan->contribution_margin_percent }}%</strong>
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('revenueplanner.plans.show', $activePlan->id) }}" class="rounded-xl bg-cyan-500 px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-cyan-600">
                            🔍 {{ __('Vollständigen Plan ansehen') }}
                        </a>
                        <a href="{{ route('revenueplanner.wizard', $activePlan->id) }}" class="rounded-xl bg-white/10 px-4 py-2.5 text-xs font-bold text-white hover:bg-white/20">
                            ✏️ {{ __('Plan anpassen') }}
                        </a>
                    </div>
                </div>

                <!-- 4 Top KPIs -->
                <div class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-4 border-t border-white/10 pt-6">
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">{{ __('Ist-Umsatz') }}</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-400">{{ number_format($activePlan->actual_revenue_annual, 0, ',', '.') }} €</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ number_format($activePlan->actual_revenue_monthly, 0, ',', '.') }} € / {{ __('Monat') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">{{ __('Umsatzlücke') }}</p>
                        <p class="mt-1 text-2xl font-bold {{ $activePlan->revenue_gap_annual > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                            {{ number_format($activePlan->revenue_gap_annual, 0, ',', '.') }} €
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $activePlan->coverage_ratio_percent }}% {{ __('Zielerreichung') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">{{ __('Bestandskunden-Hebel') }}</p>
                        <p class="mt-1 text-2xl font-bold text-amber-400">+{{ number_format($activePlan->existing_customer_potential_annual, 0, ',', '.') }} €</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ __('Restlücke:') }} {{ number_format($activePlan->remaining_gap_annual, 0, ',', '.') }} €</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">{{ __('Benötigte Leads') }}</p>
                        <p class="mt-1 text-2xl font-bold text-cyan-400">{{ $activePlan->required_leads_monthly }} <span class="text-xs font-normal text-slate-300">/ {{ __('Monat') }}</span></p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $activePlan->required_won_deals_monthly }} {{ __('Abschlüsse / Mon.') }}</p>
                    </div>
                </div>
            </div>

            <!-- 90-Day Action Roadmap & Plans Overview -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- 90-Day Action Plan (2 Columns) -->
                <div class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ __('90-Tage Maßnahmenplan zur Lückenschließung') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('Priorisierte Handlungsempfehlungen für Vertrieb, Bestandskunden und Finanzen.') }}</p>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        @foreach ($activePlan->action_roadmap ?? [] as $action)
                            <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 transition hover:border-slate-300">
                                <span class="mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-xl {{ ($action['priority'] ?? '') === 'critical' ? 'bg-rose-100 text-rose-700' : (($action['priority'] ?? '') === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700') }} text-xs font-bold">
                                    {{ strtoupper(substr($action['priority'] ?? 'M', 0, 1)) }}
                                </span>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-bold text-slate-900">{{ $action['title'] ?? '' }}</h4>
                                        <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-600 border border-slate-200">
                                            {{ $action['category'] ?? 'Allgemein' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">{{ $action['description'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- All Plans List (1 Column) -->
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-900">{{ __('Gespeicherte Pläne') }}</h3>
                        <a href="{{ route('revenueplanner.wizard') }}" class="text-xs font-bold text-[#ff9200] hover:underline">+ {{ __('Neu') }}</a>
                    </div>

                    <div class="space-y-3 pt-2">
                        @foreach ($plans as $p)
                            <a href="{{ route('revenueplanner.plans.show', $p->id) }}" class="block rounded-2xl border p-4 transition {{ $p->id === $activePlan->id ? 'border-cyan-300 bg-cyan-50/40' : 'border-slate-100 bg-slate-50 hover:border-slate-300' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900">{{ $p->name }}</span>
                                    <span class="text-[10px] font-semibold text-slate-500">{{ $p->fiscal_year }}</span>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-xs">
                                    <span class="text-slate-500">{{ __('Ziel:') }} {{ number_format($p->target_revenue_annual, 0, ',', '.') }} €</span>
                                    <span class="font-bold {{ $p->revenue_gap_annual > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $p->coverage_ratio_percent }}%
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
