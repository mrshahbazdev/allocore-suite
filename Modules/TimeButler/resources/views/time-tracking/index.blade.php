@extends('layouts.shell')

@section('title', __('Time Tracking'))
@section('page-title', __('Time Tracking'))

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Time Tracking') }}</h1>
            <div class="mt-4 flex gap-3">
                @if ($openEntry)
                    <form method="POST" action="{{ route('timebutler.time-tracking.clock-out') }}">
                        @csrf
                        <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Clock Out') }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('timebutler.time-tracking.clock-in') }}">
                        @csrf
                        <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Clock In') }}</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Entries') }}</h2>
                <table class="mt-4 min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="pb-2 pr-4">{{ __('Date') }}</th>
                            <th class="pb-2 pr-4">{{ __('Start') }}</th>
                            <th class="pb-2 pr-4">{{ __('End') }}</th>
                            <th class="pb-2 pr-4">{{ __('Break') }}</th>
                            <th class="pb-2 pr-4 text-right">{{ __('Duration') }}</th>
                            <th class="pb-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($entries as $entry)
                            <tr>
                                <td class="py-2 pr-4">{{ $entry->date->format('Y-m-d') }}</td>
                                <td class="py-2 pr-4">{{ $entry->start_time }}</td>
                                <td class="py-2 pr-4">{{ $entry->end_time ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $entry->break_minutes }} min</td>
                                <td class="py-2 pr-4 text-right">{{ $entry->durationMinutes() ? number_format($entry->durationMinutes() / 60, 2) : '-' }} h</td>
                                <td class="py-2">
                                    <form method="POST" action="{{ route('timebutler.time-tracking.destroy', $entry) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-rose-600">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">{{ $entries->links() }}</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Entry') }}</h2>
                <form method="POST" action="{{ route('timebutler.time-tracking.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Date') }}</label>
                        <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-sm font-medium text-slate-700">{{ __('Start') }}</label><input type="time" name="start_time" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                        <div><label class="block text-sm font-medium text-slate-700">{{ __('End') }}</label><input type="time" name="end_time" class="mt-1 w-full rounded-lg border-slate-300"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Break (min)') }}</label>
                        <input type="number" name="break_minutes" value="0" min="0" class="mt-1 w-full rounded-lg border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                        <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                    </div>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
