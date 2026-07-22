@extends('layouts.shell')

@section('title', $task->exists ? __('Edit Task') : __('New Task'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $task->exists ? __('Edit Task') : __('New Task') }}</h1>
        <form method="POST" action="{{ $task->exists ? route('bunnyband.admin.tasks.update', $task) : route('bunnyband.admin.tasks.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($task->exists) @method('PUT') @endif
            <div><input type="text" name="title" value="{{ old('title', $task->title) }}" placeholder="Title" class="w-full rounded-lg border-slate-300" required></div>
            <div><textarea name="description" placeholder="Description" rows="3" class="w-full rounded-lg border-slate-300" required>{{ old('description', $task->description) }}</textarea></div>
            <div class="grid gap-4 sm:grid-cols-3">
                <select name="type" class="rounded-lg border-slate-300">
                    @foreach (['social_follow' => 'Social Follow', 'app_install' => 'App Install', 'website_visit' => 'Website Visit', 'video_watch' => 'Video Watch', 'game_play' => 'Game Play', 'daily_checkin' => 'Daily Checkin'] as $key => $label)
                        <option value="{{ $key }}" {{ old('type', $task->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" name="reward" value="{{ old('reward', $task->reward) }}" placeholder="Reward" class="rounded-lg border-slate-300" required>
                <select name="verification_method" class="rounded-lg border-slate-300">
                    @foreach (['manual' => 'Manual', 'automatic' => 'Automatic', 'timer' => 'Timer'] as $key => $label)
                        <option value="{{ $key }}" {{ old('verification_method', $task->verification_method) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div><input type="url" name="url" value="{{ old('url', $task->url) }}" placeholder="URL" class="w-full rounded-lg border-slate-300"></div>
            <div class="grid gap-4 sm:grid-cols-3">
                <input type="number" name="max_completions" value="{{ old('max_completions', $task->max_completions) }}" placeholder="Max completions" class="rounded-lg border-slate-300">
                <input type="number" name="cooldown_hours" value="{{ old('cooldown_hours', $task->cooldown_hours ?? 24) }}" placeholder="Cooldown hours" class="rounded-lg border-slate-300" required>
                <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $task->is_active) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm">{{ __('Active') }}</span></label>
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
