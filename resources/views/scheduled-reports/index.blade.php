@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Scheduled Reports') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Reports that run and email automatically.') }}</p>
        </div>
        <a href="{{ route('scheduled-reports.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('New report') }}</a>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Title') }}</th>
                    <th class="px-4 py-3">{{ __('Type') }}</th>
                    <th class="px-4 py-3">{{ __('Frequency') }}</th>
                    <th class="px-4 py-3">{{ __('Format') }}</th>
                    <th class="px-4 py-3">{{ __('Next run') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($reports as $report)
                    <tr>
                        <td class="px-4 py-3 text-slate-900">{{ $report->title }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $report->report_type }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ __(ucfirst($report->frequency)) }}</td>
                        <td class="px-4 py-3 uppercase text-slate-600">{{ $report->format }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $report->next_run_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $report->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $report->is_active ? __('Active') : __('Paused') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('scheduled-reports.edit', $report) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">{{ __('No scheduled reports yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $reports->links() }}</div>
@endsection
