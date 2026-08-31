@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Admin Dashboard') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Control landing page, users, teams, plans, modules, and CMS from one place.') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.landing.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                {{ __('Landing Page Builder') }}
            </a>
            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                {{ __('View Live Site') }}
            </a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Users'), 'value' => $stats['users'], 'route' => 'admin.users.index'],
            ['label' => __('Teams'), 'value' => $stats['teams'], 'route' => 'admin.teams.index'],
            ['label' => __('Active modules'), 'value' => $stats['modules'], 'route' => 'admin.modules.index'],
            ['label' => __('Knowledge Base'), 'value' => $stats['glossary'], 'route' => 'admin.glossary.index'],
            ['label' => __('Blog Posts'), 'value' => $stats['posts'], 'route' => 'admin.blog.posts.index'],
            ['label' => __('CMS Pages'), 'value' => $stats['pages'], 'route' => 'admin.pages.index'],
            ['label' => __('Plans'), 'value' => $stats['plans'], 'route' => 'admin.plans.index'],
            ['label' => __('Subscriptions'), 'value' => $stats['subscriptions'], 'route' => 'admin.subscriptions.index'],
            ['label' => __('Pending bank'), 'value' => $stats['pending_bank']],
            ['label' => __('Analyses'), 'value' => $stats['analyses'], 'route' => 'admin.financial.index'],
            ['label' => __('Audits'), 'value' => $stats['audits'], 'route' => 'admin.audits.index'],
            ['label' => __('Admins'), 'value' => $stats['admins']],
        ] as $stat)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-slate-500">{{ $stat['label'] }}</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <div class="text-3xl font-bold text-slate-900">{{ $stat['value'] }}</div>
                    @if (! empty($stat['route']))
                        <a href="{{ route($stat['route']) }}" class="text-xs font-medium text-indigo-600 hover:underline">{{ __('Manage') }}</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
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
                            <div class="capitalize">{{ $subscription->status }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('No subscriptions yet.') }}</div>
                @endforelse
            </div>
        </div>

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
