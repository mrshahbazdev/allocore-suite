@extends('layouts.shell')

@section('title', $calendarEvent->title)
@section('page-title', $calendarEvent->title)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <a href="{{ route('planhive.calendar.index', ['month' => $calendarEvent->start_at->format('Y-m')]) }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ __('Calendar') }}</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $calendarEvent->title }}</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('planhive.calendar-events.edit', $calendarEvent) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('planhive.calendar-events.destroy', $calendarEvent) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Start') }}</div>
                    <div class="font-medium text-slate-900">{{ $calendarEvent->start_at->format('M d, Y H:i') }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('End') }}</div>
                    <div class="font-medium text-slate-900">{{ $calendarEvent->end_at?->format('M d, Y H:i') ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('All day') }}</div>
                    <div class="font-medium text-slate-900">{{ $calendarEvent->all_day ? __('Yes') : __('No') }}</div>
                </div>
                @if ($calendarEvent->project)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Project') }}</div>
                        <a href="{{ route('planhive.projects.show', $calendarEvent->project) }}" class="font-medium text-indigo-600 hover:underline">{{ $calendarEvent->project->name }}</a>
                    </div>
                @endif
            </div>
            @if ($calendarEvent->description)
                <p class="mt-6 whitespace-pre-line text-slate-700">{{ $calendarEvent->description }}</p>
            @endif
        </div>
    </div>
@endsection
