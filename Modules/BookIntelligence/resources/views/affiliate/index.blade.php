@php $title = __('Affiliate Recommendation & Monetization Engine'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6" data-no-navigate>
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 8 — Affiliate Recommendation Engine') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('Book Affiliate Monetization & Performance') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    {{ __('Monetize your thought leadership and audit reports through contextual Amazon and publisher book affiliate links with real-time click and conversion tracking.') }}
                </p>
            </div>
        </div>

        <!-- Metrics Overview -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-400">{{ __('Total Clicks') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['total_clicks'] }}</p>
            </div>
            <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-blue-700">{{ __('Estimated Conversions') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-blue-900">{{ $stats['total_conversions'] }}</p>
            </div>
            <div class="rounded-2xl border border-purple-200 bg-purple-50/50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-purple-700">{{ __('Conversion Rate') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-purple-900">{{ $stats['conversion_rate'] }}%</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-emerald-700">{{ __('Estimated Commission') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-emerald-900">€{{ number_format($stats['total_revenue'], 2) }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Top Clicked Books Leaderboard -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
                <h3 class="text-base font-bold text-slate-900">{{ __('Top Performing Books') }}</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($stats['top_books'] as $item)
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-3">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-slate-900 truncate">{{ $item['book']->title }}</h4>
                                <p class="text-[11px] text-slate-500">{{ $item['book']->author?->name ?? 'Author' }}</p>
                            </div>
                            <div class="text-right pl-3">
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">{{ $item['clicks'] }} clicks</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">{{ __('No affiliate clicks recorded yet.') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Click Traffic Log -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h3 class="text-base font-bold text-slate-900">{{ __('Real-Time Affiliate Traffic Log') }}</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase text-slate-400">
                            <tr>
                                <th class="p-2.5">{{ __('Book') }}</th>
                                <th class="p-2.5">{{ __('Source') }}</th>
                                <th class="p-2.5">{{ __('Referrer / IP') }}</th>
                                <th class="p-2.5">{{ __('Time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentClicks as $click)
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-900">{{ $click->book->title }}</td>
                                    <td class="p-2.5">
                                        <span class="rounded bg-blue-50 px-2 py-0.5 font-semibold text-blue-700 capitalize">{{ $click->source_type }}</span>
                                    </td>
                                    <td class="p-2.5 text-slate-400">{{ Str::limit($click->referrer_url ?: $click->ip_address, 25) }}</td>
                                    <td class="p-2.5 text-slate-400">{{ $click->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-400">{{ __('No recent click events.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
