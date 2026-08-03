@extends('layouts.shell')

@section('title', $contact->name)
@section('page-title', $contact->name)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                @if ($contact->project)
                    <a href="{{ route('planhive.contacts.index', $contact->project) }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ __('Contacts') }}</a>
                @endif
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $contact->name }}</h1>
                <p class="text-sm text-slate-500">{{ $contact->company }} — {{ $contact->job_title }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('planhive.contacts.edit', $contact) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('planhive.contacts.destroy', $contact) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Details') }}</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    @if ($contact->email)
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('Email') }}</dt><dd class="text-slate-900">{{ $contact->email }}</dd></div>
                    @endif
                    @if ($contact->phone)
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('Phone') }}</dt><dd class="text-slate-900">{{ $contact->phone }}</dd></div>
                    @endif
                    @if ($contact->company)
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('Company') }}</dt><dd class="text-slate-900">{{ $contact->company }}</dd></div>
                    @endif
                    @if ($contact->job_title)
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('Job Title') }}</dt><dd class="text-slate-900">{{ $contact->job_title }}</dd></div>
                    @endif
                    @if ($contact->address)
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('Address') }}</dt><dd class="text-slate-900">{{ $contact->address }}</dd></div>
                    @endif
                    @if ($contact->tags)
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('Tags') }}</dt><dd class="text-slate-900">{{ $contact->tags }}</dd></div>
                    @endif
                </dl>
            </div>

            @if ($contact->notes)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Notes') }}</h2>
                    <p class="mt-4 whitespace-pre-line text-sm text-slate-700">{{ $contact->notes }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
