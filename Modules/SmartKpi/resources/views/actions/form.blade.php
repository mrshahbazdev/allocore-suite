@extends('layouts.shell')

@section('title', $action->exists ? __('Edit Action') : __('New Action'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $action->exists ? __('Edit Action') : __('New Action') }}</h1>
        <form method="POST" action="{{ $action->exists ? route('smartkpi.actions.update', $action) : route('smartkpi.problems.actions.store', $problem) }}" class="mt-6 space-y-4">
            @csrf
            @if ($action->exists) @method('PUT') @endif

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $action->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label><textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $action->description) }}</textarea></div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Assigned To') }}</label>
                    <select name="assigned_to" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to', $action->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Priority') }}</label>
                    <select name="priority" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach (['low', 'medium', 'high'] as $p)
                            <option value="{{ $p }}" {{ old('priority', $action->priority) === $p ? 'selected' : '' }}>{{ __($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach (['open', 'in_progress', 'done', 'cancelled'] as $s)
                            <option value="{{ $s }}" {{ old('status', $action->status) === $s ? 'selected' : '' }}>{{ __($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Due Date') }}</label><input type="date" name="due_date" value="{{ old('due_date', $action->due_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Effectiveness Score (0-100)') }}</label><input type="number" name="effectiveness_score" min="0" max="100" value="{{ old('effectiveness_score', $action->effectiveness_score) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
