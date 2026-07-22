@extends('layouts.shell')

@section('title', $contact->exists ? __('Edit Contact') : __('New Contact'))
@section('page-title', $contact->exists ? __('Edit Contact') : __('New Contact'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $contact->exists ? __('Edit Contact') : __('New Contact') }}</h1>
        <form method="POST" action="{{ $contact->exists ? route('planhive.contacts.update', $contact) : route('planhive.contacts.store', $project) }}" class="mt-6 space-y-4">
            @csrf
            @if ($contact->exists)
                @method('PUT')
            @endif

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label><input type="text" name="name" value="{{ old('name', $contact->name) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Email') }}</label><input type="email" name="email" value="{{ old('email', $contact->email) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Phone') }}</label><input type="text" name="phone" value="{{ old('phone', $contact->phone) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Company') }}</label><input type="text" name="company" value="{{ old('company', $contact->company) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Job Title') }}</label><input type="text" name="job_title" value="{{ old('job_title', $contact->job_title) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Address') }}</label><textarea name="address" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('address', $contact->address) }}</textarea></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label><textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('notes', $contact->notes) }}</textarea></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Tags (comma separated)') }}</label><input type="text" name="tags" value="{{ old('tags', $contact->tags) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
