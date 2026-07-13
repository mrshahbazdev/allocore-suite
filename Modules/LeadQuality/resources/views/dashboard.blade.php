@extends('layouts.shell')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('LeadOS') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Lead generation, CRM pipeline, sequences, and ICP scoring.') }}</p>
            </div>
            <div class="flex gap-3 text-sm">
                <a href="{{ route('leadquality.contacts.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white">{{ __('Add contact') }}</a>
                <a href="{{ route('leadquality.icp.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 font-medium text-slate-700">{{ __('ICP profile') }}</a>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-200">
                <div class="text-sm text-slate-500">{{ __('Total leads') }}</div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">{{ $snapshot['total_leads'] }}</div>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-200">
                <div class="text-sm text-slate-500">{{ __('Good leads') }}</div>
                <div class="mt-2 text-3xl font-semibold text-emerald-600">{{ $snapshot['good_leads'] }}</div>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-200">
                <div class="text-sm text-slate-500">{{ __('Average score') }}</div>
                <div class="mt-2 text-3xl font-semibold text-indigo-600">{{ $snapshot['avg_score'] }}</div>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-200">
                <div class="text-sm text-slate-500">{{ __('Active sequences') }}</div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">{{ $snapshot['active_sequences'] }}</div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Pipeline overview') }}</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    @foreach ($snapshot['pipeline'] as $stage => $count)
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <div class="text-slate-500">{{ str_replace('_', ' ', ucfirst($stage)) }}</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent activities') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($snapshot['recent_activities'] as $activity)
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <div class="flex items-center justify-between text-sm">
                                <div class="font-medium text-slate-900">{{ $activity->contact?->name ?? __('Unknown contact') }}</div>
                                <div class="text-slate-500">{{ $activity->type }}</div>
                            </div>
                            <div class="mt-1 text-sm text-slate-600">{{ $activity->notes }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">{{ __('No recent activities yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
