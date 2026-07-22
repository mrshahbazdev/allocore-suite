@extends('layouts.shell')

@section('title', __('Absence'))
@section('page-title', __('Absence Request'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $absence->absenceType->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $absence->user->name }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold capitalize" style="background-color: {{ $absence->absenceType->color }}20; color: {{ $absence->absenceType->color }}">{{ __($absence->status) }}</span>
        </div>

        <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-slate-500">{{ __('Start') }}</dt><dd class="font-medium">{{ $absence->start_date->format('Y-m-d') }}</dd></div>
            <div><dt class="text-slate-500">{{ __('End') }}</dt><dd class="font-medium">{{ $absence->end_date->format('Y-m-d') }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Days') }}</dt><dd class="font-medium">{{ number_format($absence->total_days, 1) }}</dd></div>
            @if ($absence->substitute)
                <div><dt class="text-slate-500">{{ __('Substitute') }}</dt><dd class="font-medium">{{ $absence->substitute->name }}</dd></div>
            @endif
            @if ($absence->approved_at)
                <div><dt class="text-slate-500">{{ __('Approved at') }}</dt><dd class="font-medium">{{ $absence->approved_at->format('Y-m-d H:i') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Approver') }}</dt><dd class="font-medium">{{ $absence->approver?->name ?? '-' }}</dd></div>
            @endif
        </dl>

        @if ($absence->notes)
            <div class="mt-4 text-sm"><span class="text-slate-500">{{ __('Notes:') }}</span> {{ $absence->notes }}</div>
        @endif

        <div class="mt-6 flex flex-wrap gap-3">
            @if ($absence->isPending())
                <form method="POST" action="{{ route('timebutler.absences.approve', $absence) }}">
                    @csrf
                    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Approve') }}</button>
                </form>

                <form method="POST" action="{{ route('timebutler.absences.reject', $absence) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="rejection_reason" placeholder="{{ __('Reason') }}" class="rounded-lg border-slate-300 text-sm" required>
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Reject') }}</button>
                </form>
            @endif

            @if (! in_array($absence->status, ['cancelled', 'rejected']))
                <form method="POST" action="{{ route('timebutler.absences.cancel', $absence) }}">
                    @csrf
                    <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Cancel') }}</button>
                </form>
            @endif

            <form method="POST" action="{{ route('timebutler.absences.destroy', $absence) }}">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
@endsection
