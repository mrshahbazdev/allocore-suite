@extends('layouts.shell')

@section('title', __('Time Check'))
@section('page-title', __('Time Check Dashboard'))

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('Time Check') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Dashboard') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Vacation, absence & time tracking for your team.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('timebutler.absences.create') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500">{{ __('New absence') }}</a>
                <a href="{{ route('timebutler.time-tracking.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Track time') }}</a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <a href="{{ route('timebutler.calendar.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Team calendar') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg></div>
                <div class="mt-1 text-lg font-semibold text-slate-900">{{ __('Open calendar') }}</div>
            </a>
            <a href="{{ route('timebutler.absences.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Absences') }}</div><svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div class="mt-1 text-lg font-semibold text-slate-900">{{ __('Manage requests') }}</div>
            </a>
            <a href="{{ route('timebutler.time-tracking.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Time tracking') }}</div><svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div class="mt-1 text-lg font-semibold text-slate-900">{{ $openTimeEntry ? __('Clocked in') : __('Clock in/out') }}</div>
            </a>
            <a href="{{ route('timebutler.reports.absences') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Reports') }}</div><svg class="h-5 w-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></div>
                <div class="mt-1 text-lg font-semibold text-slate-900">{{ __('Export PDF') }}</div>
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Vacation Balance') }}</h2>
                @if ($balance)
                    <div class="mt-4 flex items-end gap-2">
                        <div class="text-3xl font-bold text-slate-900">{{ number_format($balance->remaining_days, 1) }}</div>
                        <div class="mb-1 text-sm text-slate-500">{{ __('of :total days left', ['total' => number_format($balance->total_days, 1)]) }}</div>
                    </div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100">
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
                        @php($statusClass = match($req->status) {
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            'rejected' => 'bg-rose-100 text-rose-700',
                            'cancelled' => 'bg-slate-100 text-slate-500',
                            default => 'bg-slate-100 text-slate-700',
                        })
                        <li class="flex items-center justify-between rounded-lg border border-slate-100 p-2 hover:bg-slate-50">
                            <span>{{ $req->start_date->format('M d') }} — {{ $req->absenceType->name }}</span>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ __($req->status) }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">{{ __('No pending requests.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Team Pending') }}</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($teamPending as $req)
                        <li class="flex items-center justify-between rounded-lg border border-slate-100 p-2 hover:bg-slate-50">
                            <span>{{ $req->user->name }} — {{ $req->absenceType->name }}</span>
                            <a href="{{ route('timebutler.absences.show', $req) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ __('Review') }}</a>
                        </li>
                    @empty
                        <li class="text-slate-500">{{ __('No pending team requests.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
