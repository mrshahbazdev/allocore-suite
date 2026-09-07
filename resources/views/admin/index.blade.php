@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Admin Mission Control') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Unified control center for all platform sub-dashboards, intelligence engines, users, billing, and system operations.') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.landing.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                {{ __('Landing Page Builder') }}
            </a>
            <a href="{{ route('admin.queue-monitor.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                {{ __('Queue Monitor') }}
            </a>
            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                {{ __('View Live Site') }}
            </a>
        </div>
    </div>

    {{-- Top Key Metrics Overview --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ([
            ['label' => __('Total Users'), 'value' => $stats['users'], 'sub' => $stats['admins'].' '.__('admins'), 'route' => 'admin.users.index', 'color' => 'indigo'],
            ['label' => __('Active Teams'), 'value' => $stats['teams'], 'sub' => __('Companies'), 'route' => 'admin.teams.index', 'color' => 'blue'],
            ['label' => __('Subscriptions'), 'value' => $stats['subscriptions'], 'sub' => ($stats['pending_bank'] > 0 ? $stats['pending_bank'].' '.__('pending bank') : $stats['active_subscriptions'].' '.__('active')), 'route' => 'admin.billing.index', 'color' => 'emerald'],
            ['label' => __('Knowledge Books'), 'value' => $stats['books'], 'sub' => $stats['gaps'].' '.__('open gaps'), 'route' => 'bookintelligence.dashboard', 'color' => 'amber'],
            ['label' => __('Financial & Audits'), 'value' => ($stats['analyses'] + $stats['audits']), 'sub' => $stats['analyses'].' '.__('fin / ').$stats['audits'].' '.__('audits'), 'route' => 'admin.financial.index', 'color' => 'purple'],
            ['label' => __('Support Tickets'), 'value' => $stats['tickets'], 'sub' => __('Open inquiries'), 'route' => 'admin.support-tickets.index', 'color' => ($stats['tickets'] > 0 ? 'rose' : 'slate')],
        ] as $stat)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300 transition">
                <div class="text-[11px] font-medium uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</div>
                <div class="mt-1 flex items-baseline justify-between">
                    <div class="text-2xl font-bold text-slate-900">{{ $stat['value'] }}</div>
                    @if (! empty($stat['route']))
                        <a href="{{ route($stat['route']) }}" class="text-xs font-semibold text-indigo-600 hover:underline">{{ __('Open') }} &rarr;</a>
                    @endif
                </div>
                <div class="mt-1 text-xs text-slate-500 truncate">{{ $stat['sub'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Admin Sub-Dashboards & Intelligence Hub Grid --}}
    <div class="mt-8">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ __('Platform Sub-Dashboards & Intelligence Hubs') }}</h2>
                <p class="text-xs text-slate-500">{{ __('Direct access into specialized operational portals, analytics suites, and AI engines.') }}</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {{-- 1. Billing & Subscriptions Dashboard --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ $stats['subscriptions'] }} {{ __('Plans') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Billing & Revenue Dashboard') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Manage MRR, payments, invoices, plan tier subscriptions, and bank transfer approvals.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('admin.billing.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">{{ __('Open Billing Hub') }} &rarr;</a>
                    <a href="{{ route('admin.subscriptions.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Approvals') }}</a>
                </div>
            </div>

            {{-- 2. Knowledge Growth OS Executive Dashboard --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-amber-200 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.967 8.967 0 00-6 2.292m6-2.292v14.25m0-14.25A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                        </span>
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">16 {{ __('AI Modules') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Knowledge Growth OS') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('AI Book Intelligence, executive knowledge growth metrics, question mapping, and team learning.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('bookintelligence.dashboard') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-800">{{ __('Open Knowledge OS') }} &rarr;</a>
                    <a href="{{ route('bookintelligence.books.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Library') }}</a>
                </div>
            </div>

            {{-- 3. Knowledge Gaps & Acquisition Engine --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-orange-200 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        </span>
                        <span class="rounded-full bg-orange-50 px-2 py-0.5 text-xs font-semibold text-orange-700">{{ $stats['gaps'] }} {{ __('Open Gaps') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Knowledge Gaps & Acquisition') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Identify organizational blindspots, missing expertise, and AI-recommended reading acquisitions.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('bookintelligence.gaps.index') }}" class="text-xs font-semibold text-orange-600 hover:text-orange-800">{{ __('Manage Gaps') }} &rarr;</a>
                    <a href="{{ route('bookintelligence.search.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Deep Search') }}</a>
                </div>
            </div>

            {{-- 4. AI Content & SEO Opportunities --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </span>
                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">{{ $stats['generated_blogs'] }} {{ __('Blogs') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('AI SEO & Content Engine') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Automated 8-section SEO blog generation, keyword clustering, and thought leadership articles.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('bookintelligence.content.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">{{ __('Content Engine') }} &rarr;</a>
                    <a href="{{ route('admin.blog.posts.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Blog Admin') }}</a>
                </div>
            </div>

            {{-- 5. Affiliate Monetization Dashboard --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-purple-200 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.106V8.78M2.25 18.75c.936.135 1.905.211 2.9.211M2.25 18.75c.657.097 1.335.145 2.04.145M18.75 9.286V6.375a2.25 2.25 0 00-1.244-2.013l-2.9-1.449A2.25 2.25 0 0012 2.25a2.25 2.25 0 00-2.1 1.663l-2.9 1.449A2.25 2.25 0 005.625 6.375v2.91M18.75 9.286h-3.375M5.625 9.286h3.375m6.75-6.896v4.5"/></svg>
                        </span>
                        <span class="rounded-full bg-purple-50 px-2 py-0.5 text-xs font-semibold text-purple-700">{{ __('Monetization') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Affiliate Monetization Hub') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Amazon/Bookshop affiliate links, click tracking, conversion metrics, and recurring commissions.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('bookintelligence.affiliate.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800">{{ __('Affiliate Hub') }} &rarr;</a>
                    <a href="{{ route('bookintelligence.repurposing.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Repurposing') }}</a>
                </div>
            </div>

            {{-- 6. Competency & Career Framework --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-teal-200 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-teal-100 text-teal-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        </span>
                        <span class="rounded-full bg-teal-50 px-2 py-0.5 text-xs font-semibold text-teal-700">{{ $stats['competency_roles'] }} {{ __('Roles') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Competencies & Career Ladders') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Team role matrix, 5-level career progressions, skill assessments, and practical challenges.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('bookintelligence.competency.index') }}" class="text-xs font-semibold text-teal-600 hover:text-teal-800">{{ __('Career Matrix') }} &rarr;</a>
                    <a href="{{ route('bookintelligence.learning.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Learning Paths') }}</a>
                </div>
            </div>

            {{-- 7. Financial Platform --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-cyan-200 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-100 text-cyan-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6"/></svg>
                        </span>
                        <span class="rounded-full bg-cyan-50 px-2 py-0.5 text-xs font-semibold text-cyan-700">{{ $stats['analyses'] }} {{ __('Analyses') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Financial Platform') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Portfolio-wide financial valuations, company health scoring, and DCF/LBO models.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('admin.financial.index') }}" class="text-xs font-semibold text-cyan-600 hover:text-cyan-800">{{ __('View Financial') }} &rarr;</a>
                    <a href="{{ route('admin.thresholds.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Thresholds') }}</a>
                </div>
            </div>

            {{-- 8. AuditPro & Compliance --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $stats['audits'] }} {{ __('Audits') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('AuditPro & Compliance') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Manage audit templates, pillar frameworks, scoring questions, and team audit reports.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('admin.audits.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">{{ __('Manage Audits') }} &rarr;</a>
                    <a href="{{ route('admin.audits.templates.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Templates') }}</a>
                </div>
            </div>

            {{-- 9. Platform Analytics & Traffic --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        </span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ __('Live Traffic') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Analytics & Visitors') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Real-time pageviews, unique visitors, browser breakdowns, and top landing page conversions.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('admin.analytics.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900">{{ __('View Analytics') }} &rarr;</a>
                    <a href="{{ route('admin.exports.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Exports') }}</a>
                </div>
            </div>

            {{-- 10. Support Tickets & Helpdesk --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-rose-200 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                        </span>
                        <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">{{ $stats['tickets'] }} {{ __('Open') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Support Desk & Tickets') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Customer inquiries, support conversations, priority escalation, and resolution tracking.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('admin.support-tickets.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-800">{{ __('Open Helpdesk') }} &rarr;</a>
                    <a href="{{ route('admin.notifications.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Broadcasts') }}</a>
                </div>
            </div>

            {{-- 11. Security & Activity Logs --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        </span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $stats['logs'] }} {{ __('Events') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Security & Audit Logs') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Full immutable event trail of user logins, impersonations, data edits, and system events.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('admin.activity-logs.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900">{{ __('View Audit Logs') }} &rarr;</a>
                    <a href="{{ route('admin.session-manager.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Sessions') }}</a>
                </div>
            </div>

            {{-- 12. System Health & Backups --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>
                        </span>
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Online') }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Backups & Maintenance') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Generate database dumps, export user/team data, and toggle platform maintenance.') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('admin.backups.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900">{{ __('Backups Hub') }} &rarr;</a>
                    <a href="{{ route('admin.log-viewer.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('Error Logs') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Suite Modules Fast Launcher --}}
    @if (!empty($activeModulesList) && $activeModulesList->count() > 0)
        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ __('Active Enterprise Modules') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('Launch any enabled workspace module directly.') }}</p>
                </div>
                <a href="{{ route('admin.modules.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">{{ __('Manage Modules') }} &rarr;</a>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($activeModulesList as $mod)
                    <a href="{{ url('app/'.$mod->route_prefix) }}" class="flex flex-col items-center justify-center rounded-lg border border-slate-100 bg-slate-50/70 p-3 text-center transition hover:border-indigo-300 hover:bg-indigo-50/50">
                        <span class="text-xs font-semibold text-slate-800">{{ $mod->name }}</span>
                        <span class="mt-1 text-[10px] text-slate-400">/app/{{ $mod->route_prefix }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Real-Time Data & Activity Streams Grid --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        {{-- Recent Users --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent users') }}</h2>
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('All users') }}</a>
            </div>
            <div class="space-y-3">
                @forelse ($recentUsers as $user)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <div>
                            <div class="font-medium text-slate-900">{{ $user->name }}</div>
                            <div class="text-sm text-slate-500">{{ $user->email }}</div>
                        </div>
                        <div class="text-right text-sm text-slate-500">
                            <div>{{ $user->currentTeam?->name ?? '—' }}</div>
                            <div>{{ $user->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('No users found.') }}</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Subscriptions --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent subscriptions') }}</h2>
                <a href="{{ route('admin.subscriptions.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('All subscriptions') }}</a>
            </div>
            <div class="space-y-3">
                @forelse ($recentSubscriptions as $subscription)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <div>
                            <div class="font-medium text-slate-900">{{ $subscription->billable?->name ?? '—' }}</div>
                            <div class="text-sm text-slate-500">{{ $subscription->plan?->name ?? '—' }}</div>
                        </div>
                        <div class="text-right text-sm text-slate-500">
                            <div class="capitalize">{{ $subscription->payment_method }}</div>
                            <div class="capitalize font-semibold {{ $subscription->status === 'active' ? 'text-emerald-600' : ($subscription->status === 'pending' ? 'text-amber-600' : 'text-slate-500') }}">{{ $subscription->status }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('No subscriptions yet.') }}</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Analyses --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent analyses') }}</h2>
                <a href="{{ route('admin.financial.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Financial') }}</a>
            </div>
            <div class="space-y-3">
                @forelse ($recentAnalyses as $analysis)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <div>
                            <div class="font-medium text-slate-900">{{ $analysis->name }}</div>
                            <div class="text-sm text-slate-500">{{ $analysis->team?->name ?? '—' }}</div>
                        </div>
                        <div class="text-right text-sm text-slate-500">
                            <div class="capitalize">{{ $analysis->typeLabel() }}</div>
                            <div>{{ $analysis->total_score !== null ? number_format($analysis->total_score, 1) : '—' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('No analyses yet.') }}</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Audits --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent audits') }}</h2>
                <a href="{{ route('admin.audits.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('AuditPro') }}</a>
            </div>
            <div class="space-y-3">
                @forelse ($recentAudits as $audit)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <div>
                            <div class="font-medium text-slate-900">{{ $audit->template?->name ?? '—' }}</div>
                            <div class="text-sm text-slate-500">{{ $audit->team?->name ?? '—' }}</div>
                        </div>
                        <div class="text-right text-sm text-slate-500">
                            <div class="capitalize">{{ $audit->status }}</div>
                            <div>{{ $audit->total_score !== null ? number_format($audit->total_score, 1) : '—' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('No audits yet.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

