@extends('layouts.shell')

@section('title', __('Contacts'))
@section('page-title', __('Contacts'))

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('LeadOS') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Contacts') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Manage leads and track qualification signals.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('leadquality.contacts.analyze-all') }}">
                    @csrf
                    <button class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500">{{ __('Analyze all') }}</button>
                </form>
                <a href="{{ route('leadquality.contacts.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('New contact') }}</a>
                <a href="{{ route('leadquality.contacts.create') }}#import" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Import CSV') }}</a>
            </div>
        </div>

        @if ($contacts->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                {{ __('No contacts yet. Add or import your first lead.') }}
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('Name') }}</th>
                            <th class="px-4 py-3">{{ __('Company') }}</th>
                            <th class="px-4 py-3">{{ __('Score') }}</th>
                            <th class="px-4 py-3">{{ __('Pipeline') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($contacts as $contact)
                            @php($score = $contact->analysis['total_score'] ?? 0)
                            @php($scoreClass = $score >= 70 ? 'bg-emerald-100 text-emerald-700' : ($score >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700'))
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $contact->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $contact->company }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $scoreClass }}">{{ $score }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ str_replace('_', ' ', $contact->pipeline_stage) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('leadquality.contacts.show', $contact) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @endif
    </div>
@endsection
