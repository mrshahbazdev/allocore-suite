@extends('layouts.shell')

@section('title', __('Reports'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Production Reports') }}</h1>

        <form method="GET" class="flex flex-wrap gap-3">
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">{{ __('Update') }}</button>
            <a href="{{ route('dentaltrack.admin.reports.export-orders', ['from' => $from, 'to' => $to]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Export Orders CSV') }}</a>
            <a href="{{ route('dentaltrack.admin.reports.export-scans', ['from' => $from, 'to' => $to]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Export Scans CSV') }}</a>
        </form>

        <div class="grid gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Orders Created') }}</div><div class="text-2xl font-bold">{{ $orders }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Completed') }}</div><div class="text-2xl font-bold">{{ $completed }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Scan Events') }}</div><div class="text-2xl font-bold">{{ $scans }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Avg Step Duration (min)') }}</div><div class="text-2xl font-bold">{{ $avgStepDuration !== null ? round($avgStepDuration / 60, 1) : '-' }}</div></div>
        </div>
    </div>
@endsection
