@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Data Exports') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('Download platform data as CSV with optional date filters.') }}</p>

        <form method="GET" action="{{ route('admin.exports.download') }}" class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Export type') }}</label>
                <select name="type" required class="mt-2 w-full rounded-lg border-slate-300 text-sm">
                    <option value="users">{{ __('Users') }}</option>
                    <option value="teams">{{ __('Teams') }}</option>
                    <option value="subscriptions">{{ __('Subscriptions') }}</option>
                    <option value="invoices">{{ __('Invoices') }}</option>
                    <option value="payments">{{ __('Payments') }}</option>
                    <option value="activity-logs">{{ __('Activity Logs') }}</option>
                </select>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Start date') }}</label>
                    <input type="date" name="start" class="mt-2 w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('End date') }}</label>
                    <input type="date" name="end" class="mt-2 w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Download CSV') }}</button>
        </form>
    </div>
@endsection
