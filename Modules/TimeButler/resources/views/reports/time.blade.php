@extends('layouts.shell')

@section('title', __('Time Report'))
@section('page-title', __('Time Report'))

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Time Report') }}</h1>
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
                <a href="{{ route('timebutler.reports.time', request()->merge(['format' => 'pdf'])->all()) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('PDF') }}</a>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-500">{{ __('Total Hours') }}</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($totalMinutes / 60, 2) }} h</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Employee') }}</th><th class="pb-2 pr-4">{{ __('Date') }}</th><th class="pb-2 pr-4">{{ __('Start') }}</th><th class="pb-2 pr-4">{{ __('End') }}</th><th class="pb-2 pr-4 text-right">{{ __('Duration') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($items as $item)
                        <tr>
                            <td class="py-2 pr-4">{{ $item->user->name }}</td>
                            <td class="py-2 pr-4">{{ $item->date->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4">{{ $item->start_time }}</td>
                            <td class="py-2 pr-4">{{ $item->end_time ?? '-' }}</td>
                            <td class="py-2 pr-4 text-right">{{ $item->durationMinutes() ? number_format($item->durationMinutes() / 60, 2) : '-' }} h</td>
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
