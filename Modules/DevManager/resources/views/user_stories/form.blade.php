@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('devmanager.user-stories.index', $idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ $idea->title }}</a>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">{{ $story ? __('Edit User Story') : __('New User Story') }}</h1>

    <form method="POST" action="{{ $story ? route('devmanager.user-stories.update', $story) : route('devmanager.user-stories.store', $idea) }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if($story) @method('PUT') @endif

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Requirement') }} ({{ __('optional') }})</label>
            <select name="requirement_id" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                <option value="">—</option>
                @foreach($idea->requirements as $requirement)
                    <option value="{{ $requirement->id }}" {{ old('requirement_id', $story?->requirement_id) == $requirement->id ? 'selected' : '' }}>{{ $requirement->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <x-form-field name="role" label="As a" required :value="old('role', $story?->role)" />
            <x-form-field name="action" label="I want" required :value="old('action', $story?->action)" />
        </div>

        <x-form-field name="benefit" label="So that" :value="old('benefit', $story?->benefit)" />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Acceptance criteria') }}</label>
            <textarea name="acceptance_criteria" rows="4" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('acceptance_criteria', $story?->acceptance_criteria) }}</textarea>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <x-form-field type="number" name="story_points" label="Story points" :value="old('story_points', $story?->story_points)" />
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                <select name="status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['todo','in_progress','done'] as $s)
                        <option value="{{ $s }}" {{ old('status', $story?->status) == $s ? 'selected' : '' }}>{{ __($s) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-[#0094af] px-4 py-2 text-sm font-semibold text-white hover:bg-[#007a8f]">{{ $story ? __('Update') : __('Create') }}</button>
            <a href="{{ route('devmanager.user-stories.index', $idea) }}" class="text-sm text-slate-600 hover:underline">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
