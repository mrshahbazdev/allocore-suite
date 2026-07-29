@extends('layouts.shell')

@section('title', __('Calendar'))
@section('page-title', __('Calendar'))

@section('content')
    @php
        use Carbon\Carbon;
        $current = Carbon::parse($month);
        $prevMonth = $current->copy()->subMonth()->format('Y-m');
        $nextMonth = $current->copy()->addMonth()->format('Y-m');
        $today = now()->format('Y-m');
        $daysInMonth = $current->daysInMonth;
        $firstDayOfWeek = $current->copy()->startOfMonth()->dayOfWeek;
        $weeks = (int) ceil(($firstDayOfWeek + $daysInMonth) / 7);
        $totalCells = $weeks * 7;
        $blankEnd = $totalCells - ($firstDayOfWeek + $daysInMonth);
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('PlanHive') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Calendar') }}</h1>
                <p class="mt-1 text-lg text-slate-600">{{ $current->format('F Y') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('planhive.calendar.index', ['month' => $today]) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Today') }}</a>
                <a href="{{ route('planhive.calendar.index', ['month' => $prevMonth]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50" aria-label="{{ __('Previous month') }}">{{ __('&larr;') }}</a>
                <a href="{{ route('planhive.calendar.index', ['month' => $nextMonth]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50" aria-label="{{ __('Next month') }}">{{ __('&rarr;') }}</a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="grid grid-cols-7 gap-px border-b border-slate-200 bg-slate-100 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <div class="bg-white py-3">{{ __('Sun') }}</div>
                    <div class="bg-white py-3">{{ __('Mon') }}</div>
                    <div class="bg-white py-3">{{ __('Tue') }}</div>
                    <div class="bg-white py-3">{{ __('Wed') }}</div>
                    <div class="bg-white py-3">{{ __('Thu') }}</div>
                    <div class="bg-white py-3">{{ __('Fri') }}</div>
                    <div class="bg-white py-3">{{ __('Sat') }}</div>
                </div>
                <div class="grid grid-cols-7 gap-px bg-slate-100">
                    @for ($i = 0; $i < $firstDayOfWeek; $i++)
                        <div class="min-h-24 bg-slate-50 sm:min-h-32"></div>
                    @endfor

                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php($day = $current->copy()->startOfMonth()->addDays($d - 1))
                        @php($dayEvents = $events->filter(fn ($e) => $e->start_at->isSameDay($day))->values())
                        <div class="group flex min-h-24 flex-col justify-between bg-white p-2 transition hover:bg-slate-50 sm:min-h-32">
                            <div class="flex justify-end">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold {{ $day->isToday() ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-700 group-hover:text-indigo-700' }}">{{ $d }}</span>
                            </div>
                            <div class="mt-1 flex flex-col gap-1">
                                @foreach ($dayEvents->take(2) as $event)
                                    @php($chipColor = $event->project?->color ?? '#4f46e5')
                                    <a href="{{ route('planhive.calendar-events.edit', $event) }}" class="truncate rounded-md border border-slate-100 bg-indigo-50 px-2 py-1 text-xs font-medium leading-tight text-indigo-700 transition hover:bg-indigo-100" title="{{ $event->title }}">
                                        <span class="mr-1 inline-block h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: {{ $chipColor }}"></span>
                                        <span class="truncate">{{ $event->title }}</span>
                                    </a>
                                @endforeach
                                @if ($dayEvents->count() > 2)
                                    <span class="text-xs font-medium text-slate-500" title="{{ $dayEvents->slice(2)->pluck('title')->implode(', ') }}">+{{ $dayEvents->count() - 2 }} {{ __('more') }}</span>
                                @endif
                            </div>
                        </div>
                    @endfor

                    @for ($i = 0; $i < $blankEnd; $i++)
                        <div class="min-h-24 bg-slate-50 sm:min-h-32"></div>
                    @endfor
                </div>

                @if ($events->isEmpty())
                    <div class="flex flex-col items-center justify-center border-t border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500">
                        <svg class="mb-2 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        {{ __('No events this month. Add one from the panel on the right.') }}
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Event') }}</h2>
                    <p class="text-sm text-slate-500">{{ __('Schedule a team event inside a project.') }}</p>
                    <form method="POST" action="{{ route('planhive.calendar.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Project') }}</label>
                            <select name="project_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('Select project') }}</option>
                                @foreach ($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                            <input type="text" name="title" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">{{ __('Start') }}</label>
                                <input type="datetime-local" name="start_at" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">{{ __('End') }}</label>
                                <input type="datetime-local" name="end_at" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                            <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="all_day" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                {{ __('All day') }}
                            </label>
                        </div>
                        <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Save Event') }}</button>
                    </form>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Upcoming') }}</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        @forelse ($events->sortBy('start_at')->take(10) as $event)
                            <li class="flex items-start justify-between gap-3 rounded-lg border border-slate-100 p-2 hover:bg-slate-50">
                                <div class="min-w-0">
                                    <a href="{{ route('planhive.calendar-events.edit', $event) }}" class="truncate font-medium text-indigo-600 hover:text-indigo-500">{{ $event->title }}</a>
                                    <div class="text-xs text-slate-500">{{ $event->project?->name ?? __('No project') }}</div>
                                </div>
                                <div class="shrink-0 text-right text-xs text-slate-500">
                                    <div>{{ $event->start_at->format('M d') }}</div>
                                    <div>{{ $event->all_day ? __('All day') : $event->start_at->format('H:i') }}</div>
                                </div>
                            </li>
                        @empty
                            <li class="text-slate-500">{{ __('No upcoming events this month.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
