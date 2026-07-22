@extends('layouts.shell')

@section('title', __('Absence Report'))
@section('page-title', __('Absence Report'))

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Absence Report') }}</h1>
            <form method="GET" class="mt-4 flex flex-wrap items-end gap-3">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Start') }}</label><input type="date" name="start" value="{{ $start->format('Y-m-d') }}" class="mt-1 rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('End') }}</label><input type="date" name="end" value="{{ $end->format('Y-m-d') }}" class="mt-1 rounded-lg border-slate-300"></div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('User') }}</label>
                    <select name="user_id" class="mt-1 rounded-lg border-slate-300">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
                <a href="{{ route('timebutler.reports.absences', request()->merge(['format' => 'pdf'])->all()) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('PDF') }}</a>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($summary as $row)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase text-slate-500">{{ $row['type'] }}</div>
                    <div class="mt-1 text-xl font-bold text-slate-900">{{ number_format($row['days'], 1) }} {{ __('days') }}</div>
                    <div class="text-xs text-slate-500">{{ $row['count'] }} {{ __('requests') }}</div>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Employee') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2 pr-4">{{ __('From') }}</th><th class="pb-2 pr-4">{{ __('To') }}</th><th class="pb-2 pr-4 text-right">{{ __('Days') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($items as $item)
                        <tr>
                            <td class="py-2 pr-4">{{ $item->user->name }}</td>
                            <td class="py-2 pr-4">{{ $item->absenceType->name }}</td>
                            <td class="py-2 pr-4">{{ $item->start_date->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4">{{ $item->end_date->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($item->total_days, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($items instanceof \Illuminate\Contracts\Pagination\Paginator)
                <div class="mt-4">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
@endsection
