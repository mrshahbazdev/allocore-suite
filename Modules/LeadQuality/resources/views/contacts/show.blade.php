@extends('layouts.shell')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">{{ $contact->name }}</h1>
                        <p class="text-sm text-slate-500">{{ $contact->company }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-slate-500">{{ __('Lead score') }}</div>
                        <div class="text-3xl font-semibold text-indigo-600">{{ $contact->analysis['total_score'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="mt-4 text-sm text-slate-600">{{ $contact->notes }}</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('AI insights') }}</h2>
                <ul class="mt-4 space-y-2 text-sm text-slate-600">
                    @foreach ($aiInsights['insights'] as $insight)
                        <li class="rounded-lg bg-slate-50 px-3 py-2">{{ $insight }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Activities') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($contact->activities as $activity)
                        <div class="rounded-xl border border-slate-200 px-4 py-3 text-sm">
                            <div class="font-medium text-slate-900">{{ $activity->type }} · {{ $activity->status }}</div>
                            <div class="mt-1 text-slate-600">{{ $activity->notes }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">{{ __('No activities logged yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Log activity') }}</h2>
                <form method="POST" action="{{ route('leadquality.contacts.activities.store', $contact) }}" class="mt-4 space-y-3">
                    @csrf
                    <select name="type" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="outreach">{{ __('Outreach') }}</option>
                        <option value="follow-up">{{ __('Follow-up') }}</option>
                        <option value="meeting">{{ __('Meeting') }}</option>
                        <option value="reminder">{{ __('Reminder') }}</option>
                    </select>
                    <textarea name="notes" rows="4" class="w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('Notes') }}"></textarea>
                    <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">{{ __('Log') }}</button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Run AI analysis') }}</h2>
                <form method="POST" action="{{ route('leadquality.contacts.analyze', $contact) }}" class="mt-4">
                    @csrf
                    <button class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Analyze lead') }}</button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Enrolled sequences') }}</h2>
                <div class="mt-4 space-y-2 text-sm text-slate-600">
                    @forelse ($contact->sequences as $sequence)
                        <div class="rounded-lg bg-slate-50 px-3 py-2">{{ $sequence->name }}</div>
                    @empty
                        <div>{{ __('Not enrolled yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
