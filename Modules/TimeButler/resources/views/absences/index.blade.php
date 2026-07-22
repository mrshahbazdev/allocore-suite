@extends('layouts.shell')

@section('title', __('Absences'))
@section('page-title', __('Absences'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Absence Requests') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Request, track and approve absences.') }}</p>
            </div>
            <a href="{{ route('timebutler.absences.create') }}" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Request') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 rounded-lg border-slate-300">
                        <option value="">{{ __('All') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
                    <select name="type" class="mt-1 rounded-lg border-slate-300">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">{{ __('Employee') }}</th>
                        <th class="pb-2 pr-4">{{ __('Type') }}</th>
                        <th class="pb-2 pr-4">{{ __('From') }}</th>
                        <th class="pb-2 pr-4">{{ __('To') }}</th>
                        <th class="pb-2 pr-4 text-right">{{ __('Days') }}</th>
                        <th class="pb-2 pr-4">{{ __('Status') }}</th>
                        <th class="pb-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($requests as $req)
                        <tr>
                            <td class="py-2 pr-4">{{ $req->user->name }}</td>
                            <td class="py-2 pr-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium" style="background-color: {{ $req->absenceType->color }}20; color: {{ $req->absenceType->color }}">
                                    {{ $req->absenceType->name }}
                                </span>
                            </td>
                            <td class="py-2 pr-4">{{ $req->start_date->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4">{{ $req->end_date->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($req->total_days, 1) }}</td>
                            <td class="py-2 pr-4"><span class="capitalize">{{ __($req->status) }}</span></td>
                            <td class="py-2"><a href="{{ route('timebutler.absences.show', $req) }}" class="text-indigo-600">{{ __('View') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $requests->links() }}</div>
        </div>
    </div>
@endsection
