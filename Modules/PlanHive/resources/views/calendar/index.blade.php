@extends('layouts.shell')

@section('title', __('Calendar'))
@section('page-title', __('Calendar'))

@section('content')
    @php
        use Carbon\Carbon;
        $current = Carbon::parse($month);
        $prevMonth = $current->copy()->subMonth()->format('Y-m');
        $nextMonth = $current->copy()->addMonth()->format('Y-m');
        $daysInMonth = $current->daysInMonth;
        $firstDayOfWeek = $current->copy()->startOfMonth()->dayOfWeek;
        $weeks = (int) ceil(($firstDayOfWeek + $daysInMonth) / 7);
        $totalCells = $weeks * 7;
        $blankEnd = $totalCells - ($firstDayOfWeek + $daysInMonth);
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('PlanHive') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Calendar') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $current->format('F Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('planhive.calendar.index', ['month' => $prevMonth]) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Previous') }}</a>
                <a href="{{ route('planhive.calendar.index', ['month' => $nextMonth]) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Next') }}</a>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Event') }}</h2>
            </div>
            <form method="POST" action="{{ route('planhive.calendar.store') }}" class="grid gap-4 md:grid-cols-6">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">{{ __('Project') }}</label>
                    <select name="project_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">{{ __('Select project') }}</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                    <input type="text" name="title" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Start') }}</label>
                    <input type="datetime-local" name="start_at" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('End') }}</label>
                    <input type="datetime-local" name="end_at" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Save') }}</button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid grid-cols-7 gap-px overflow-hidden rounded-t-2xl border-b border-slate-200 bg-slate-100 text-center text-xs font-semibold uppercase text-slate-500">
                <div class="bg-white py-2">{{ __('Sun') }}</div>
                <div class="bg-white py-2">{{ __('Mon') }}</div>
                <div class="bg-white py-2">{{ __('Tue') }}</div>
                <div class="bg-white py-2">{{ __('Wed') }}</div>
                <div class="bg-white py-2">{{ __('Thu') }}</div>
                <div class="bg-white py-2">{{ __('Fri') }}</div>
                <div class="bg-white py-2">{{ __('Sat') }}</div>
            </div>
            <div class="grid grid-cols-7 gap-px overflow-hidden rounded-b-2xl bg-slate-100">
                @for ($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="min-h-[100px] bg-slate-50"></div>
                @endfor

                @for ($d = 1; $d <= $daysInMonth; $d++)
                    @php($day = $current->copy()->startOfMonth()->addDays($d - 1))
                    @php($dayEvents = $events->filter(fn ($e) => $e->start_at->isSameDay($day))->values())
                    <div class="min-h-[100px] bg-white p-2 transition hover:bg-slate-50">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold {{ $day->isToday() ? 'flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white' : 'text-slate-700' }}">{{ $d }}</span>
                        </div>
                        <div class="mt-1 space-y-1">
                            @foreach ($dayEvents as $event)
                                <a href="{{ route('planhive.calendar-events.edit', $event) }}" class="block truncate rounded bg-indigo-50 px-1.5 py-0.5 text-xs text-indigo-700 transition hover:bg-indigo-100" title="{{ $event->title }}">{{ $event->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endfor

                @for ($i = 0; $i < $blankEnd; $i++)
                    <div class="min-h-[100px] bg-slate-50"></div>
                @endfor
            </div>

            @if ($events->isEmpty())
                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                    {{ __('No events this month. Use the form above to add an event.') }}
                </div>
            @endif
        </div>
    </div>
@endsection
