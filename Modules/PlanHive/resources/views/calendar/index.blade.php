@extends('layouts.shell')

@section('title', __('Calendar'))
@section('page-title', __('Calendar'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Calendar') }}</h1>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Title') }}</th><th class="pb-2 pr-4">{{ __('Project') }}</th><th class="pb-2 pr-4">{{ __('Start') }}</th><th class="pb-2 pr-4">{{ __('End') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($events as $event)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $event->title }}</td>
                            <td class="py-2 pr-4">{{ $event->project?->name ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $event->start_at->format('Y-m-d H:i') }}</td>
                            <td class="py-2 pr-4">{{ $event->end_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="py-2"><a href="{{ route('planhive.calendar-events.edit', $event) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
