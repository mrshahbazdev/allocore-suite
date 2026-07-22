@extends('layouts.shell')

@section('title', $event->exists ? __('Edit Event') : __('New Event'))
@section('page-title', $event->exists ? __('Edit Event') : __('New Event'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $event->exists ? __('Edit Event') : __('New Event') }}</h1>
        <form method="POST" action="{{ $event->exists ? route('planhive.calendar-events.update', $event) : route('planhive.calendar-events.store', $project) }}" class="mt-6 space-y-4">
            @csrf
            @if ($event->exists)
                @method('PUT')
            @endif

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $event->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $event->description) }}</textarea></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Start') }}</label><input type="datetime-local" name="start_at" value="{{ old('start_at', $event->start_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('End') }}</label><input type="datetime-local" name="end_at" value="{{ old('end_at', $event->end_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="all_day" value="1" {{ old('all_day', $event->all_day) ? 'checked' : '' }} class="rounded border-slate-300"> {{ __('All day') }}</label>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
