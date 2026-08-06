@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('devmanager.requirements.index', $idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ $idea->title }}</a>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">{{ $requirement ? __('Edit Requirement') : __('New Requirement') }}</h1>

    <form method="POST" action="{{ $requirement ? route('devmanager.requirements.update', $requirement) : route('devmanager.requirements.store', $idea) }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if($requirement) @method('PUT') @endif

        <x-form-field name="title" label="Title" required :value="old('title', $requirement?->title)" />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
            <textarea name="description" rows="4" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('description', $requirement?->description) }}</textarea>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Priority') }}</label>
                <select name="priority" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['low','medium','high'] as $p)
                        <option value="{{ $p }}" {{ old('priority', $requirement?->priority) == $p ? 'selected' : '' }}>{{ __($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                <select name="status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['draft','review','done'] as $s)
                        <option value="{{ $s }}" {{ old('status', $requirement?->status) == $s ? 'selected' : '' }}>{{ __($s) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ $requirement ? __('Update') : __('Create') }}</button>
            <a href="{{ route('devmanager.requirements.index', $idea) }}" class="text-sm text-slate-600 hover:underline">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
