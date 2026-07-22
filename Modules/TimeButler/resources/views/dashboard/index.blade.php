@extends('layouts.shell')

@section('title', __('TimeButler'))
@section('page-title', __('TimeButler Dashboard'))

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('TimeButler') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Vacation, absence & time tracking.') }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('timebutler.absences.create') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                <div class="text-sm font-medium text-slate-500">{{ __('New absence') }}</div>
                <div class="mt-2 text-lg font-semibold text-indigo-600">{{ __('Request vacation') }}</div>
            </a>
            <a href="{{ route('timebutler.calendar.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                <div class="text-sm font-medium text-slate-500">{{ __('Calendar') }}</div>
                <div class="mt-2 text-lg font-semibold text-indigo-600">{{ __('Team view') }}</div>
            </a>
            <a href="{{ route('timebutler.time-tracking.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                <div class="text-sm font-medium text-slate-500">{{ __('Time tracking') }}</div>
                <div class="mt-2 text-lg font-semibold text-indigo-600">{{ $openTimeEntry ? __('Clocked in') : __('Clock in/out') }}</div>
            </a>
            <a href="{{ route('timebutler.reports.absences') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                <div class="text-sm font-medium text-slate-500">{{ __('Reports') }}</div>
                <div class="mt-2 text-lg font-semibold text-indigo-600">{{ __('PDF export') }}</div>
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Vacation Balance') }}</h2>
                @if ($balance)
                    <div class="mt-4 flex items-end gap-2">
                        <div class="text-3xl font-bold text-slate-900">{{ number_format($balance->remaining_days, 1) }}</div>
                        <div class="text-sm text-slate-500 mb-1">{{ __('of :total days left', ['total' => number_format($balance->total_days, 1)]) }}</div>
                    </div>
                    <div class="mt-2 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        @php($percent = $balance->total_days > 0 ? min(100, ($balance->taken_days / $balance->total_days) * 100) : 0)
                        <div class="h-full rounded-full bg-indigo-600" style="width: {{ $percent }}%"></div>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-sm">
                        <div><div class="font-semibold">{{ number_format($balance->taken_days, 1) }}</div><div class="text-slate-500">{{ __('Taken') }}</div></div>
                        <div><div class="font-semibold">{{ number_format($balance->requested_days, 1) }}</div><div class="text-slate-500">{{ __('Requested') }}</div></div>
                        <div><div class="font-semibold">{{ number_format($balance->remaining_days, 1) }}</div><div class="text-slate-500">{{ __('Left') }}</div></div>
                    </div>
                @else
                    <p class="mt-4 text-sm text-slate-500">{{ __('No vacation balance set. Add one from the admin or settings.') }}</p>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('My Pending') }}</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($pendingRequests as $req)
                        <li class="flex justify-between"><span>{{ $req->start_date->format('M d') }} — {{ $req->absenceType->name }}</span><span class="text-amber-600">{{ __($req->status) }}</span></li>
                    @empty
                        <li class="text-slate-500">{{ __('No pending requests.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Team Pending') }}</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($teamPending as $req)
                        <li class="flex justify-between"><span>{{ $req->user->name }} — {{ $req->absenceType->name }}</span><a href="{{ route('timebutler.absences.show', $req) }}" class="text-indigo-600">{{ __('Review') }}</a></li>
                    @empty
                        <li class="text-slate-500">{{ __('No pending team requests.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
