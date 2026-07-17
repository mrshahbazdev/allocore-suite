@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.activity_logs.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.activity_logs.description') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex flex-wrap items-center gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.activity_logs.search_placeholder') }}" class="flex-1 rounded-lg border-slate-300 text-sm min-w-[200px]">
            <select name="log_name" class="rounded-lg border-slate-300 text-sm">
                <option value="">{{ __('admin.activity_logs.all_types') }}</option>
                @foreach ($logNames as $name)
                    <option value="{{ $name }}" @selected(request('log_name') === $name)>{{ ucfirst($name) }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('admin.activity_logs.time') }}</th>
                    <th class="px-4 py-3">{{ __('admin.activity_logs.type') }}</th>
                    <th class="px-4 py-3">{{ __('admin.activity_logs.description') }}</th>
                    <th class="px-4 py-3">{{ __('Causer') }}</th>
                    <th class="px-4 py-3">{{ __('Team') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-slate-600">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-3"><span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $log->log_name }}</span></td>
                        <td class="px-4 py-3 text-slate-900">{{ Str::limit($log->description, 80) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $log->causer?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $log->team?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.activity-logs.show', $log) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">{{ __('admin.activity_logs.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
