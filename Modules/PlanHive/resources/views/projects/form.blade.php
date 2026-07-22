@extends('layouts.shell')

@section('title', $project->exists ? __('Edit Project') : __('New Project'))
@section('page-title', $project->exists ? __('Edit Project') : __('New Project'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $project->exists ? __('Edit Project') : __('New Project') }}</h1>

        <form method="POST" action="{{ $project->exists ? route('planhive.projects.update', $project) : route('planhive.projects.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($project->exists)
                @method('PUT')
            @endif

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $project->description) }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Color') }}</label><input type="color" name="color" value="{{ old('color', $project->color ?? '#6366f1') }}" class="mt-1 h-10 w-full rounded-lg border-slate-300"></div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="archived" {{ old('status', $project->status) === 'archived' ? 'selected' : '' }}>{{ __('Archived') }}</option>
                        <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Start Date') }}</label><input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('End Date') }}</label><input type="date" name="end_date" value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
