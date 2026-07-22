@extends('layouts.shell')

@section('title', $goal->exists ? __('Edit Goal') : __('New Goal'))
@section('page-title', $goal->exists ? __('Edit Goal') : __('New Goal'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $goal->exists ? __('Edit Goal') : __('New Goal') }}</h1>
        <form method="POST" action="{{ $goal->exists ? route('planhive.goals.update', $goal) : route('planhive.goals.store', $project) }}" class="mt-6 space-y-4">
            @csrf
            @if ($goal->exists)
                @method('PUT')
            @endif

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $goal->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label><textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $goal->description) }}</textarea></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Target Date') }}</label><input type="date" name="target_date" value="{{ old('target_date', $goal->target_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Progress (%)') }}</label><input type="number" name="progress" min="0" max="100" value="{{ old('progress', $goal->progress ?? 0) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                <select name="status" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="active" {{ old('status', $goal->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="achieved" {{ old('status', $goal->status) === 'achieved' ? 'selected' : '' }}>{{ __('Achieved') }}</option>
                    <option value="dropped" {{ old('status', $goal->status) === 'dropped' ? 'selected' : '' }}>{{ __('Dropped') }}</option>
                </select>
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
