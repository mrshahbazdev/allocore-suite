@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Activity Timeline') }}</h1>
        <p class="text-sm text-slate-500">{{ __('A unified timeline of actions across your workspace.') }}</p>
    </div>

    <div class="space-y-6">
        @forelse ($logs->groupBy(fn ($log) => $log->created_at->format('Y-m-d')) as $date => $dayLogs)
            <div>
                <h2 class="sticky top-0 mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ \Illuminate\Support\Carbon::parse($date)->isoFormat('dddd, MMMM D, YYYY') }}</h2>
                <div class="space-y-3">
                    @foreach ($dayLogs as $log)
                        <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0Z" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-800">
                                    <span class="font-semibold">{{ $log->log_name }}</span>
                                    — {{ $log->description }}
                                </p>
                                <div class="mt-1 flex items-center gap-3 text-xs text-slate-500">
                                    <span>{{ $log->created_at->diffForHumans() }}</span>
                                    @if ($log->causer)
                                        <span>{{ __('by') }} {{ $log->causer->name ?? __('System') }}</span>
                                    @endif
                                    @if ($log->subject)
                                        <span>{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No activity recorded yet.') }}</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $logs->links() }}</div>
@endsection
