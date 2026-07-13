@extends('layouts.shell')

@section('content')
    <div class="max-w-4xl">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $mode === 'edit' ? __('Edit contact') : __('New contact') }}</h1>
        </div>

        <form method="POST" action="{{ $mode === 'edit' ? route('leadquality.contacts.update', $contact) : route('leadquality.contacts.store') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                @foreach (['name','company','position','email','website','linkedin','industry','role','source'] as $field)
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">{{ ucfirst(str_replace('_', ' ', $field)) }}</span>
                        <input name="{{ $field }}" value="{{ old($field, $contact->{$field}) }}" class="w-full rounded-lg border-slate-300" />
                    </label>
                @endforeach
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Priority') }}</span>
                    <input name="priority" type="number" min="1" max="5" value="{{ old('priority', $contact->priority ?? 1) }}" class="w-full rounded-lg border-slate-300" />
                </label>
                <label class="block md:col-span-2">
                    <span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</span>
                    <textarea name="notes" rows="4" class="w-full rounded-lg border-slate-300">{{ old('notes', $contact->notes) }}</textarea>
                </label>
            </div>

            <div class="flex gap-3">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">{{ __('Save') }}</button>
                <a href="{{ route('leadquality.contacts.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Cancel') }}</a>
            </div>
        </form>

        <div id="import" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Import CSV') }}</h2>
            <form method="POST" action="{{ route('leadquality.contacts.import') }}" enctype="multipart/form-data" class="mt-4 flex items-center gap-3">
                @csrf
                <input type="file" name="csv_file" accept=".csv,text/csv" class="block w-full text-sm text-slate-600" />
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Import') }}</button>
            </form>
        </div>
    </div>
@endsection
