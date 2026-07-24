@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Activity Timeline') }}</h1>
        <p class="text-sm text-slate-500">{{ __('A unified timeline of actions across your workspace.') }}</p>
    </div>

    <form method="GET" action="{{ route('timeline.index') }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search description...') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Log name') }}</label>
                <select name="log_name" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($logNames as $name)
                        <option value="{{ $name }}" {{ request('log_name') === $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Team member') }}</label>
                <select name="causer_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    <option value="">{{ __('All members') }}</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" {{ request('causer_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('From') }}</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('To') }}</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <a href="{{ route('timeline.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Reset') }}</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Filter') }}</button>
        </div>
    </form>

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
