@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $workflow->exists ? __('Edit workflow') : __('Create workflow') }}</h1>
    </div>

    <form method="POST" action="{{ $workflow->exists ? route('workflows.update', $workflow) : route('workflows.store') }}" class="max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($workflow->exists) @method('PATCH') @endif

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $workflow->name) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Trigger event') }}</label>
                <select name="trigger_event" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="created" {{ old('trigger_event', $workflow->trigger_event) === 'created' ? 'selected' : '' }}>{{ __('created') }}</option>
                    <option value="updated" {{ old('trigger_event', $workflow->trigger_event) === 'updated' ? 'selected' : '' }}>{{ __('updated') }}</option>
                    <option value="deleted" {{ old('trigger_event', $workflow->trigger_event) === 'deleted' ? 'selected' : '' }}>{{ __('deleted') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Action') }}</label>
                <select name="action" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="send_notification" {{ old('action', $workflow->action) === 'send_notification' ? 'selected' : '' }}>{{ __('Send notification') }}</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Subject type contains') }}</label>
            <input type="text" name="subject_type" value="{{ old('subject_type', $workflow->subject_type) }}" placeholder="e.g. VisionFlow\\Models\\Organization" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <p class="mt-1 text-xs text-slate-500">{{ __('Optional. Leave empty to match any model.') }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Message') }}</label>
            <textarea name="action_payload[message]" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('action_payload.message', $workflow->action_payload['message'] ?? '') }}</textarea>
        </div>

        <div class="mb-6 flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $workflow->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <label class="text-sm text-slate-700">{{ __('Active') }}</label>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ $workflow->exists ? __('Update') : __('Save') }}</button>
            <a href="{{ route('workflows.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection
