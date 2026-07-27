@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('Financial Platform') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Finance Dashboard') }}</h1>
        </div>
        <a href="{{ route('gmbh.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            {{ __('New analysis') }}
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Companies') }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['companies'] }}</p>
            <a href="{{ route('companies.create') }}" class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('Add company') }} →</a>
        </div>

        <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('GmbH analyses') }}</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['gmbh'] }}</p>
            <a href="{{ route('gmbh.create') }}" class="mt-3 inline-block text-sm font-medium text-emerald-600 hover:text-emerald-700">{{ __('New analysis') }} →</a>
        </div>

        <div class="rounded-xl border border-amber-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Annual reports') }}</p>
            <p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['jahresabschluss'] }}</p>
            <a href="{{ route('jahresabschluss.create') }}" class="mt-3 inline-block text-sm font-medium text-amber-600 hover:text-amber-700">{{ __('Create report') }} →</a>
        </div>

        <div class="rounded-xl border border-purple-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Real estate') }}</p>
            <p class="mt-2 text-3xl font-bold text-purple-600">{{ $stats['immobilien'] }}</p>
            <a href="{{ route('immobilien.create') }}" class="mt-3 inline-block text-sm font-medium text-purple-600 hover:text-purple-700">{{ __('Analyze') }} →</a>
        </div>

        <div class="rounded-xl border border-sky-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Leads') }}</p>
            <p class="mt-2 text-3xl font-bold text-sky-600">{{ $stats['leads'] }}</p>
            <a href="{{ route('leads.create') }}" class="mt-3 inline-block text-sm font-medium text-sky-600 hover:text-sky-700">{{ __('New lead') }} →</a>
        </div>

        <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('PayPal revenue') }}</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($stats['paypal_revenue'], 0, ',', '.') }} €</p>
            <a href="{{ route('paypal.index') }}" class="mt-3 inline-block text-sm font-medium text-emerald-600 hover:text-emerald-700">{{ __('Transactions') }} →</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Recent analyses --}}
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent analyses') }}</h2>
                <a href="{{ route('analyses.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('All analyses') }} →</a>
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
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $typeColors[$a->type] ?? 'text-slate-600 bg-slate-100' }}">
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

        {{-- Quick start + companies --}}
        <div class="space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Quick start') }}</h2>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('gmbh.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Start GmbH analysis') }}</a>
                    <a href="{{ route('jahresabschluss.create') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Create annual report') }}</a>
                    <a href="{{ route('immobilien.create') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Analyze real estate') }}</a>
                    <a href="{{ route('companies.create') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Add company') }}</a>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
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
@endsection
