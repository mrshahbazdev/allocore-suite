@extends('layouts.shell')

@section('title', $task->exists ? __('Edit Task') : __('New Task'))
@section('page-title', $task->exists ? __('Edit Task') : __('New Task'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $task->exists ? __('Edit Task') : __('New Task') }}</h1>
        <form method="POST" action="{{ $task->exists ? route('planhive.tasks.update', $task) : route('planhive.tasks.store', $project) }}" class="mt-6 space-y-4">
            @csrf
            @if ($task->exists)
                @method('PUT')
            @endif

            <input type="hidden" name="project_id" value="{{ $project->id }}">

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $task->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label><textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $task->description) }}</textarea></div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="todo" {{ old('status', $task->status) === 'todo' ? 'selected' : '' }}>{{ __('To Do') }}</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                        <option value="done" {{ old('status', $task->status) === 'done' ? 'selected' : '' }}>{{ __('Done') }}</option>
                        <option value="cancelled" {{ old('status', $task->status) === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Priority') }}</label>
                    <select name="priority" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                        <option value="urgent" {{ old('priority', $task->priority) === 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Assigned To') }}</label><select name="assigned_to" class="mt-1 w-full rounded-lg border-slate-300"><option value="">{{ __('Unassigned') }}</option>@foreach (\App\Models\User::query()->where('current_team_id', auth()->user()->current_team_id)->get() as $u)<option value="{{ $u->id }}" {{ old('assigned_to', $task->assigned_to) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach</select></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Due Date') }}</label><input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
