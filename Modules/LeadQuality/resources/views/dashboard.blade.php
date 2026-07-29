@extends('layouts.shell')

@section('title', __('LeadOS Dashboard'))
@section('page-title', __('LeadOS Dashboard'))

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('LeadOS') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Dashboard') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Lead generation, CRM pipeline, sequences, and ICP scoring.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('leadquality.contacts.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Add contact') }}</a>
                <a href="{{ route('leadquality.icp.index') }}" class="rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('ICP profile') }}</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('leadquality.contacts.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Total leads') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.637-2.911M15 19.128V13.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 13.5v3.75m-3-1.837a6.375 6.375 0 0111.637-2.911"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $snapshot['total_leads'] }}</div>
            </a>
            <a href="{{ route('leadquality.contacts.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Good leads') }}</div><svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $snapshot['good_leads'] }}</div>
            </a>
            <a href="{{ route('leadquality.analytics') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Average score') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $snapshot['avg_score'] }}</div>
            </a>
            <a href="{{ route('leadquality.sequences.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Active sequences') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5h10.25M3.75 10.5h10.25m-10.25-3h10.25m-10.25 9.75h10.25m4.5-9.75h.008v.008H18.75V6.75zm0 3.75h.008v.008H18.75V10.5zm0 3.75h.008v.008H18.75V14.25zm0 3.75h.008v.008H18.75V18z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $snapshot['active_sequences'] }}</div>
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('leadquality.contacts.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="text-xs uppercase text-slate-500">{{ __('Contacts') }}</div>
                <div class="mt-1 text-sm text-slate-600">{{ __('CRM leads & scoring') }}</div>
            </a>
            <a href="{{ route('leadquality.pipeline') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="text-xs uppercase text-slate-500">{{ __('Pipeline') }}</div>
                <div class="mt-1 text-sm text-slate-600">{{ __('Stage & move deals') }}</div>
            </a>
            <a href="{{ route('leadquality.sequences.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="text-xs uppercase text-slate-500">{{ __('Sequences') }}</div>
                <div class="mt-1 text-sm text-slate-600">{{ __('Outreach automation') }}</div>
            </a>
            <a href="{{ route('leadquality.email-accounts.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="text-xs uppercase text-slate-500">{{ __('Email Accounts') }}</div>
                <div class="mt-1 text-sm text-slate-600">{{ __('SMTP connections') }}</div>
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Pipeline overview') }}</h2>
                    <a href="{{ route('leadquality.pipeline') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ __('View pipeline') }}</a>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($snapshot['pipeline'] as $stage => $count)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-indigo-200">
                            <div class="text-xs uppercase text-slate-500">{{ str_replace('_', ' ', ucfirst($stage)) }}</div>
                            <div class="mt-1 text-2xl font-bold text-slate-900">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Recent activities') }}</h2>
                @forelse ($snapshot['recent_activities'] as $activity)
                    <div class="flex items-start justify-between gap-3 rounded-xl border border-slate-100 p-3">
                        <div>
                            <div class="font-medium text-slate-900">{{ $activity->contact?->name ?? __('Unknown contact') }}</div>
                            <div class="text-sm text-slate-500">{{ $activity->type }} · {{ $activity->created_at?->diffForHumans() }}</div>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ __($activity->type) }}</span>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                        {{ __('No recent activities yet.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
