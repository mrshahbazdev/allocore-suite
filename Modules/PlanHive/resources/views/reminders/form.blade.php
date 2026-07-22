@extends('layouts.shell')

@section('title', $reminder->exists ? __('Edit Reminder') : __('New Reminder'))
@section('page-title', $reminder->exists ? __('Edit Reminder') : __('New Reminder'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $reminder->exists ? __('Edit Reminder') : __('New Reminder') }}</h1>
        <form method="POST" action="{{ $reminder->exists ? route('planhive.reminders.update', $reminder) : route('planhive.reminders.store', $project) }}" class="mt-6 space-y-4">
            @csrf
            @if ($reminder->exists)
                @method('PUT')
            @endif

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $reminder->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Remind At') }}</label><input type="datetime-local" name="remind_at" value="{{ old('remind_at', $reminder->remind_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>

            @if (! $reminder->exists)
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
                        <select name="remindable_type" class="mt-1 w-full rounded-lg border-slate-300">
                            <option value="project">{{ __('Project') }}</option>
                            <option value="task">{{ __('Task') }}</option>
                            <option value="goal">{{ __('Goal') }}</option>
                            <option value="note">{{ __('Note') }}</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('ID') }}</label><input type="number" name="remindable_id" value="{{ old('remindable_id', $reminder->remindable_id) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                </div>
            @else
                <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="is_done" value="1" {{ old('is_done', $reminder->is_done) ? 'checked' : '' }} class="rounded border-slate-300"> {{ __('Done') }}</label>
            @endif

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
