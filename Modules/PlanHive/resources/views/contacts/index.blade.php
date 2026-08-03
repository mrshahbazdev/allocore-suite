@extends('layouts.shell')

@section('title', __('Contacts'))
@section('page-title', __('Contacts'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Contacts') }}</h1>
            <a href="{{ route('planhive.contacts.create', $project) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Contact') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Search') }}</label>
                    <input type="text" name="search" value="{{ $search }}" class="mt-1 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($contacts as $contact)
                <div class="flex flex-col gap-2 border-b border-slate-100 p-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <a href="{{ route('planhive.contacts.show', $contact) }}" class="font-semibold text-slate-900 hover:text-indigo-600">{{ $contact->name }}</a>
                        <div class="mt-1 text-xs text-slate-500">{{ $contact->company ?? '-' }} — {{ $contact->email ?? '-' }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if ($contact->project)
                            <span class="text-xs text-slate-500">{{ $contact->project->name }}</span>
                        @endif
                        <a href="{{ route('planhive.contacts.edit', $contact) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500">{{ __('No contacts found.') }}</div>
            @endforelse
        </div>

        <div>{{ $contacts->links() }}</div>
    </div>
@endsection
