@extends('layouts.shell')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('Financial Platform') }}</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Finance Dashboard') }}</h1>
        </div>
        <a href="{{ route('gmbh.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
            {{ __('New analysis') }}
        </a>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['label' => __('Companies'), 'value' => $stats['companies'], 'route' => 'companies.index', 'color' => 'text-indigo-600', 'border' => 'border-indigo-100', 'icon' => 'M15 19.128a9.38 9.38 0 002.625.364 11.978 11.978 0 00-2.625-.364C10.444 18.626 7.647 17.13 6 15m12 0c-.816.78-1.85 1.24-3 1.382A9.38 9.38 0 0012.375 15c-2.474 0-4.75.81-6.527 2.181C4.178 17.61 3.5 17.107 3.5 16.5v-.75a2.25 2.25 0 012.25-2.25h12a2.25 2.25 0 012.25 2.25v.75c0 .607-.678 1.11-1.348.681A9.38 9.38 0 0015 19.128z M12 11.25a3 3 0 100-6 3 3 0 000 6z'],
            ['label' => __('GmbH analyses'), 'value' => $stats['gmbh'], 'route' => 'gmbh.index', 'color' => 'text-emerald-600', 'border' => 'border-emerald-100', 'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m3-15h5.25m-5.25 3h5.25m-5.25 3h5.25m3 6.75h5.25m-5.25 3h5.25m-5.25 3h5.25M6.75 3v18'],
            ['label' => __('Annual reports'), 'value' => $stats['jahresabschluss'], 'route' => 'jahresabschluss.index', 'color' => 'text-amber-600', 'border' => 'border-amber-100', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.122-.066m0 0a48.415 48.415 0 00-10.225.055 2.115 2.115 0 01-1.987 1.987v.252c0 .97.784 1.75 1.75 1.75h13.75c.966 0 1.75-.78 1.75-1.75v-.252A2.115 2.115 0 0019.503 3.84m-1.987 1.987a48.415 48.415 0 00-10.225.055M19.503 3.84l-6.36 6.36a2.25 2.25 0 01-3.182 0l-6.36-6.36'],
            ['label' => __('Real estate'), 'value' => $stats['immobilien'], 'route' => 'immobilien.index', 'color' => 'text-purple-600', 'border' => 'border-purple-100', 'icon' => 'M8.25 21v-8.25a1.5 1.5 0 00-1.5-1.5h-3a1.5 1.5 0 00-1.5 1.5V21m18 0v-8.25a1.5 1.5 0 00-1.5-1.5h-3a1.5 1.5 0 00-1.5 1.5V21m-9 0h9m-12 0H3.375A1.125 1.125 0 012.25 19.875v-9.75a1.125 1.125 0 011.125-1.125h3a1.125 1.125 0 011.125 1.125v9.75A1.125 1.125 0 016.375 21h.75m9-18v8.25a1.5 1.5 0 001.5 1.5h3a1.5 1.5 0 001.5-1.5V3m-6 0v8.25a1.5 1.5 0 001.5 1.5h3a1.5 1.5 0 001.5-1.5V3'],
            ['label' => __('Leads'), 'value' => $stats['leads'], 'route' => 'leads.index', 'color' => 'text-sky-600', 'border' => 'border-sky-100', 'icon' => 'M15 19.128a9.38 9.38 0 002.625.364 11.978 11.978 0 00-2.625-.364C10.444 18.626 7.647 17.13 6 15m12 0c-.816.78-1.85 1.24-3 1.382A9.38 9.38 0 0012.375 15c-2.474 0-4.75.81-6.527 2.181C4.178 17.61 3.5 17.107 3.5 16.5v-.75a2.25 2.25 0 012.25-2.25h12a2.25 2.25 0 012.25 2.25v.75c0 .607-.678 1.11-1.348.681A9.38 9.38 0 0015 19.128z M12 11.25a3 3 0 100-6 3 3 0 000 6z'],
            ['label' => __('PayPal revenue'), 'value' => number_format($stats['paypal_revenue'], 0, ',', '.') . ' €', 'route' => 'paypal.index', 'color' => 'text-emerald-600', 'border' => 'border-emerald-100', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.03-.659-1.171-.879-1.172-2.303 0-3.182 1.171-.879 3.07-.879 4.242 0L12 6'],
        ] as $card)
            <a href="{{ route($card['route']) }}" class="rounded-2xl border {{ $card['border'] }} bg-white p-5 shadow-sm transition hover:border-indigo-200">
                <div class="flex items-center justify-between">
                    <div class="text-xs uppercase text-slate-500">{{ $card['label'] }}</div>
                    <svg class="h-5 w-5 {{ $card['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                </div>
                <div class="mt-1 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent analyses') }}</h2>
                <a href="{{ route('analyses.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ __('All analyses') }} →</a>
            </div>

            @if ($recentAnalyses->isEmpty())
                <div class="rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-600">
                    <p class="font-semibold">{{ __('No analyses yet') }}</p>
                    <p class="mt-1 text-sm">{{ __('Start with a GmbH or real-estate analysis.') }}</p>
                    <a href="{{ route('gmbh.create') }}" class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Create first analysis') }}</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-3 py-2">{{ __('Name') }}</th>
                                <th class="px-3 py-2">{{ __('Company') }}</th>
                                <th class="px-3 py-2">{{ __('Type') }}</th>
                                <th class="px-3 py-2">{{ __('Score') }}</th>
                                <th class="px-3 py-2">{{ __('Date') }}</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recentAnalyses as $a)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-3 font-medium text-slate-900">{{ $a->name }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ $a->company->name ?? '—' }}</td>
                                    <td class="px-3 py-3">
                                        @php
                                            $typeColors = [
                                                'gmbh' => 'text-indigo-600 bg-indigo-50',
                                                'jahresabschluss' => 'text-amber-600 bg-amber-50',
                                                'immobilien' => 'text-purple-600 bg-purple-50',
                                            ];
                                        @endphp
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $typeColors[$a->type] ?? 'text-slate-600 bg-slate-100' }}">
                                            {{ $a->typeLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        @if ($a->total_score !== null)
                                            <span class="font-bold text-slate-900">{{ number_format($a->total_score, 1) }}</span>
                                            <span class="text-xs text-slate-500">/100</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-xs text-slate-500">{{ $a->created_at->format('d.m.Y') }}</td>
                                    <td class="px-3 py-3 text-right">
                                        <a href="{{ route($a->type . '.show', $a) }}" class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Quick start') }}</h2>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('gmbh.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Start GmbH analysis') }}</a>
                    <a href="{{ route('jahresabschluss.create') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Create annual report') }}</a>
                    <a href="{{ route('immobilien.create') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Analyze real estate') }}</a>
                    <a href="{{ route('companies.create') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Add company') }}</a>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Companies') }}</h2>
                    <a href="{{ route('companies.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('All') }} →</a>
                </div>
                @forelse ($companies as $company)
                    <div class="flex items-center justify-between border-b border-slate-100 py-3 last:border-b-0">
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $company->name }}</p>
                            <p class="text-xs text-slate-500">{{ $company->analyses_count }} {{ __('analyses') }}</p>
                        </div>
                        <a href="{{ route('companies.show', $company) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">→</a>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-slate-500">{{ __('No companies yet') }}</p>
                @endforelse
            </section>
        </div>
    </div>
</div>
@endsection
