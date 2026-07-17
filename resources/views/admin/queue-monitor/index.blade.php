@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.queue_monitor.title') }}</h1>
        <p class="text-sm text-slate-500">{{ __('admin.queue_monitor.description') }}</p>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('admin.queue_monitor.pending') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $counts['pending'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('admin.queue_monitor.failed') }}</div>
            <div class="mt-2 text-3xl font-bold text-rose-600">{{ $counts['failed'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('admin.queue_monitor.batches') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $counts['delayed'] }}</div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">{{ __('Connection') }}</th>
                    <th class="px-4 py-3">{{ __('Queue') }}</th>
                    <th class="px-4 py-3">{{ __('Exception') }}</th>
                    <th class="px-4 py-3">{{ __('Failed at') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($failedJobs as $job)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $job->id }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $job->connection }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $job->queue }}</td>
                        <td class="px-4 py-3 text-slate-600"><pre class="text-xs">{{ Str::limit($job->exception, 120) }}</pre></td>
                        <td class="px-4 py-3 text-slate-600">{{ $job->failed_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">{{ __('admin.queue_monitor.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $failedJobs->links() }}</div>
@endsection
