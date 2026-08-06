@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('devmanager.releases.index', $idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ $idea->title }}</a>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">{{ $release ? __('Edit Release') : __('New Release') }}</h1>

    <form method="POST" action="{{ $release ? route('devmanager.releases.update', $release) : route('devmanager.releases.store', $idea) }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if($release) @method('PUT') @endif

        <div class="grid gap-6 md:grid-cols-2">
            <x-form-field name="version" label="Version" required :value="old('version', $release?->version)" />
            <x-form-field name="title" label="Title" required :value="old('title', $release?->title)" />
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Summary') }}</label>
            <textarea name="summary" rows="3" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('summary', $release?->summary) }}</textarea>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Changelog') }}</label>
            <textarea name="changelog" rows="5" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('changelog', $release?->changelog) }}</textarea>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <x-form-field type="date" name="released_at" label="Released at" :value="old('released_at', $release?->released_at?->format('Y-m-d'))" />
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                <select name="status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['draft','planned','released','archived'] as $s)
                        <option value="{{ $s }}" {{ old('status', $release?->status) == $s ? 'selected' : '' }}>{{ __($s) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ $release ? __('Update') : __('Create') }}</button>
            <a href="{{ route('devmanager.releases.index', $idea) }}" class="text-sm text-slate-600 hover:underline">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
