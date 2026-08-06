@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-6 text-2xl font-bold text-slate-900">{{ $idea ? __('Edit Idea') : __('New Idea') }}</h1>

    <form method="POST" action="{{ $idea ? route('devmanager.ideas.update', $idea) : route('devmanager.ideas.store') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if($idea) @method('PUT') @endif

        <x-form-field name="idea[title]" label="Title" required :value="old('idea.title', $idea?->title)" />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
            <textarea name="idea[description]" rows="3" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('idea.description', $idea?->description) }}</textarea>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Problem') }}</label>
                <textarea name="idea[problem]" rows="2" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('idea.problem', $idea?->problem) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Who has this problem?') }}</label>
                <textarea name="idea[audience]" rows="2" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('idea.audience', $idea?->audience) }}</textarea>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Value created') }}</label>
                <textarea name="idea[value]" rows="2" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('idea.value', $idea?->value) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Cost of problem today') }}</label>
                <textarea name="idea[cost_of_problem]" rows="2" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('idea.cost_of_problem', $idea?->cost_of_problem) }}</textarea>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                <select name="idea[status]" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['draft','approved','in_progress','completed','archived'] as $status)
                        <option value="{{ $status }}" {{ old('idea.status', $idea?->status) == $status ? 'selected' : '' }}>{{ __($status) }}</option>
                    @endforeach
                </select>
            </div>
            <x-form-field type="date" name="idea[started_at]" label="Started at" :value="old('idea.started_at', $idea?->started_at?->format('Y-m-d'))" />
            <x-form-field type="date" name="idea[completed_at]" label="Completed at" :value="old('idea.completed_at', $idea?->completed_at?->format('Y-m-d'))" />
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ $idea ? __('Update') : __('Create') }}</button>
            <a href="{{ route('devmanager.ideas.index') }}" class="text-sm text-slate-600 hover:underline">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
