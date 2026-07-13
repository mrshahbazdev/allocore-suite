@extends('layouts.shell')

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Contacts') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Manage leads and track qualification signals.') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('leadquality.contacts.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">{{ __('New contact') }}</a>
            <a href="{{ route('leadquality.contacts.create') }}#import" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Import CSV') }}</a>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
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
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $contact->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $contact->company }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $contact->analysis['total_score'] ?? 0 }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $contact->pipeline_stage }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('leadquality.contacts.show', $contact) }}" class="text-indigo-600 hover:underline">{{ __('View') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
